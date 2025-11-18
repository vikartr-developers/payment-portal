<?php

namespace App\Console\Commands;

use App\Models\BankManagement;
use Illuminate\Console\Command;

class GenerateBankShortCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'banks:generate-short-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate short codes for bank accounts that don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accounts = BankManagement::whereNull('short_code')->orWhere('short_code', '')->get();

        $count = 0;
        foreach ($accounts as $account) {
            do {
                $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
            } while (BankManagement::where('short_code', $code)->exists());

            $account->short_code = $code;
            $account->save();
            $count++;

            $this->info("Generated short code '{$code}' for account: {$account->name} (ID: {$account->id})");
        }

        $this->info("Total short codes generated: {$count}");
        return 0;
    }
}
