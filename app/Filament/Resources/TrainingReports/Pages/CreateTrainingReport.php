<?php

namespace App\Filament\Resources\TrainingReports\Pages;

use App\Filament\Resources\TrainingReports\TrainingReportResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CreateTrainingReport extends CreateRecord
{
    protected static string $resource = TrainingReportResource::class;

    protected function afterCreate()
    {
        DB::beginTransaction();
        $parsed_file = Storage::disk('local')->path($this->form->getRawState()['parsed_path']);
        $report_information = json_decode(file_get_contents($parsed_file), true);

        // participants
        if (isset($report_information['participants'])) {
            $participants = $report_information['participants'];
            foreach ($participants as $participant) {
                $this->record->trainees()->create([
                    'name' => $participant
                ]);
            }
        }

        DB::commit();
    }
}
