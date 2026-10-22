<?php

namespace App\Console\Commands;

use App\Mail\QuestionUploadMail;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ActivateScheduledQuizzes extends Command
{
    protected $signature   = 'quizzes:activate-scheduled {--dry-run}';
    protected $description = 'Notify users about quizzes that just became available';

    public function handle(): int
    {
        $now    = now();
        $dryRun = (bool) $this->option('dry-run');

        $topics = Topic::query()
            ->whereNull('notified_at')
            ->whereNotNull('fileName')
            ->whereHas('questions', function ($q) use ($now) {
                $q->whereNotNull('published_at')
                  ->whereNull('closed_at')
                  ->where(fn ($qq) => $qq->whereNull('available_from')->orWhere('available_from', '<=', $now))
                  ->where(fn ($qq) => $qq->whereNull('available_until')->orWhere('available_until', '>',  $now));
            })
            ->get();

        if ($topics->isEmpty()) {
            $this->info('No quizzes to activate.');
            return self::SUCCESS;
        }

        $grouped = $topics->groupBy('fileName');
        $users   = User::where('role', 'USER')->get();

        if ($dryRun) {
            foreach ($grouped as $fileName => $group) {
                $this->line(" • {$fileName} → topics " . $group->pluck('id')->implode(', '));
            }
            return self::SUCCESS;
        }

        foreach ($grouped as $fileName => $group) {
            $fileUrl = asset('public/storage/questions/' . $fileName);

            foreach ($users as $index => $user) {
                Mail::to($user->email)->later(
                    now()->addSeconds($index * 20),
                    new QuestionUploadMail($user, $fileUrl)
                );
            }

            Topic::whereIn('id', $group->pluck('id'))->update(['notified_at' => now()]);

            Log::info('Scheduled quiz activated', [
                'fileName'  => $fileName,
                'topic_ids' => $group->pluck('id')->all(),
                'users'     => $users->count(),
            ]);
        }

        $this->info("Notified {$grouped->count()} upload(s) to {$users->count()} user(s).");
        return self::SUCCESS;
    }
}