<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class JwtSecretGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:jwt-generate {--key=JWT_SECRET : Key of the secret value in .env} {--show : Display the secret instead of writing to .env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $envKey = $this->option('key') ?? 'JWT_SECRET';
        // Generate a 32-byte (256-bit) random string
        $secret = bin2hex(random_bytes(32));

        if ($this->option('show')) {
            $this->info("JWT Secret: $secret");
            return;
        }

        $envPath = base_path('.env');
        $envContents = File::get($envPath);

        if (str_contains($envContents, "$envKey=")) {
            // Replace existing value
            $envContents = preg_replace(
                "/$envKey=.*/",
                "$envKey=$secret",
                $envContents
            );
        } else {
            // Append if it doesn’t exist
            $envContents .= "\n$envKey=$secret\n";
        }
        File::put($envPath, $envContents);

        $this->info("$envKey generated and saved to .env");
    }
}
