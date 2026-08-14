<?php

namespace App\Collectors\Support;

use App\Mail\SourceAlertMail;
use App\Models\Source;
use App\Models\SourceAlert;
use App\Models\SourceHealthCheck;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class SourceAlertManager
{
    public function evaluate(Source $source, SourceHealthCheck $health): void
    {
        if ($health->status === 'operational') {
            SourceAlert::query()
                ->where('source_id', $source->id)
                ->where('status', 'open')
                ->update(['status' => 'resolved', 'resolved_at' => now()]);

            return;
        }

        [$type, $severity] = $this->classify($health);
        $alert = SourceAlert::query()->firstOrNew([
            'source_id' => $source->id,
            'type' => $type,
        ]);
        $wasResolved = $alert->exists && $alert->status === 'resolved';

        if (! $alert->exists) {
            $alert->first_detected_at = $health->checked_at;
            $alert->occurrences = 0;
        }

        $alert->fill([
            'source_health_check_id' => $health->id,
            'severity' => $severity,
            'status' => 'open',
            'last_detected_at' => $health->checked_at,
            'resolved_at' => null,
            'message' => $health->message ?: 'A fonte requer revisão.',
        ]);
        $alert->occurrences++;

        if ($wasResolved) {
            $alert->notified_at = null;
            $alert->notification_status = 'pending';
            $alert->notification_error = null;
        }

        $alert->save();
        $this->notify($source, $alert);
    }

    /** @return array{string, string} */
    private function classify(SourceHealthCheck $health): array
    {
        return match (true) {
            $health->status === 'schema_changed' => ['schema_changed', 'critical'],
            $health->status === 'unavailable' => ['source_unavailable', 'critical'],
            $health->status === 'partial' && $health->records_count === 0 => ['unexpected_empty', 'warning'],
            default => ['source_partial', 'warning'],
        };
    }

    private function notify(Source $source, SourceAlert $alert): void
    {
        $recipient = config('collectors.alert_email');
        if (! is_string($recipient) || trim($recipient) === '') {
            $alert->update(['notification_status' => 'not_configured']);

            return;
        }

        if ($alert->notified_at !== null) {
            return;
        }

        try {
            Mail::to($recipient)->send(new SourceAlertMail(
                sourceName: $source->name,
                type: $alert->type,
                severity: $alert->severity,
                alertMessage: $alert->message,
                officialUrl: $source->official_url,
            ));
            $alert->update([
                'notified_at' => now(),
                'notification_status' => 'sent',
                'notification_error' => null,
            ]);
        } catch (Throwable $exception) {
            $alert->update([
                'notification_status' => 'failed',
                'notification_error' => mb_substr($exception->getMessage(), 0, 4000),
            ]);
            Log::warning('Falha ao notificar alerta de fonte.', [
                'source_id' => $source->id,
                'alert_id' => $alert->id,
                'exception' => $exception::class,
            ]);
        }
    }
}
