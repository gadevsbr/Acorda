<?php

namespace App\Console\Commands;

use App\Services\IdentityCandidateGenerator;
use Illuminate\Console\Command;

class GenerateIdentityCandidates extends Command
{
    protected $signature = 'identity:generate-candidates';

    protected $description = 'Gera candidatos revisáveis sem fundir identidades automaticamente';

    public function handle(IdentityCandidateGenerator $generator): int
    {
        $result = $generator->generate();
        $this->table(['Grupos', 'Candidatos', 'Novos'], [[$result['groups'], $result['candidates'], $result['created']]]);

        return self::SUCCESS;
    }
}
