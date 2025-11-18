<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateUserSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:generate-slugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate slugs for users who don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereNull('slug')->orWhere('slug', '')->get();

        $count = 0;
        foreach ($users as $user) {
            $firstName = $user->first_name ?: 'user';
            $slug = Str::slug($firstName);
            $originalSlug = $slug;
            $counter = 1;

            while (User::where('slug', $slug)->where('id', '!=', $user->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $user->slug = $slug;
            $user->save();
            $count++;

            $this->info("Generated slug '{$slug}' for user: {$user->name} (ID: {$user->id})");
        }

        $this->info("Total slugs generated: {$count}");
        return 0;
    }
}
