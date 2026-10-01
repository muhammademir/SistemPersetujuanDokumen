<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


class ApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = fake('id_ID');
    $applicants = DB::table('users')->where('email', 'like', 'pemohon%')->pluck('id')->all();
    $reviewers  = DB::table('users')->where('email', 'like', 'penilai%')->pluck('id')->all();

    $statuses = [
        'draft' => 15, 'submitted' => 20, 'under_review' => 15,
        'revision_required' => 10, 'approved' => 30, 'rejected' => 10,
    ];
    $pool = [];
    foreach ($statuses as $status => $weight) {
        $pool = array_merge($pool, array_fill(0, $weight, $status));
    }

    $types = ['SLF', 'AMDAL', 'IMB', 'UKL-UPL', 'SIUP'];

    DB::transaction(function () use ($faker, $applicants, $reviewers, $pool, $types) {
        $rows = [];
        for ($i = 1; $i <= 10_000; $i++) {
            $status    = $pool[array_rand($pool)];
            $createdAt = $faker->dateTimeBetween('-2 years', 'now');
            $isDraft   = $status === 'draft';
            $isDecided = in_array($status, ['approved', 'rejected'], true);

            $rows[] = [
                'code'                 => sprintf('PMH-%s-%06d', $createdAt->format('Y'), $i),
                'applicant_id'         => $applicants[array_rand($applicants)],
                'assigned_reviewer_id' => $isDraft ? null : $reviewers[array_rand($reviewers)],
                'title'                => 'Permohonan '.$faker->randomElement($types).' '.$faker->company(),
                'description'          => $faker->paragraph(3),
                'document_type'        => $faker->randomElement($types),
                'status'               => $status,
                'revision_count'       => $status === 'revision_required' ? random_int(1, 3) : 0,
                'submitted_at'         => $isDraft ? null : $createdAt->format('Y-m-d H:i:s'),
                'decided_at'           => $isDecided ? $createdAt->format('Y-m-d H:i:s') : null,
                'created_at'           => $createdAt->format('Y-m-d H:i:s'),
                'updated_at'           => $createdAt->format('Y-m-d H:i:s'),
            ];
        }

        foreach (array_chunk($rows, 1000) as $chunk) {
            DB::table('applications')->insert($chunk);
        }
    });

    $this->command->info('10.000 permohonan berhasil dibuat.');
    }
}
