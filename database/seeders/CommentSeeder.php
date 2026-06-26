<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Comment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authors = User::whereHas('roles', fn($q) => $q->whereIn('slug', ['recruiter', 'hiring-manager', 'candidate']))->get();
        $applications = Application::all();

        if ($authors->isEmpty() || $applications->isEmpty())
            return;

        for ($i = 0; $i < 50; $i++) {
            Comment::factory()->create([
                'application_id' => $applications->random()->id,
                'author_id' => $authors->random()->id,
            ]);
        }
    }
}
