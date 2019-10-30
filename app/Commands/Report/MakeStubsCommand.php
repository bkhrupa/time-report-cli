<?php

namespace App\Commands;

use App\Commands\Report\ReportBase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeStubsCommand extends ReportBase
{

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $name = 'report:make-stubs';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Makes example CSV files.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->prepareReportsPath();

        foreach (File::allFiles(app_path('Systems')) as $file) {
            $system = str_replace('.php', '', $file->getFilename());

            if ($system == 'AbstractSystem') {
                continue;
            }

            // Systems/CodebaseLemberg.php -> codebase-lemberg
            $dir = Str::kebab($system);
            $dirPath = $this->reportsPath . DIRECTORY_SEPARATOR . $dir;

            if (!File::exists($dirPath)) {
                File::makeDirectory($dirPath);
            }

            $stubFile = 'dummy-project-name.csv';
            $stubContent = '0;3;\'This is a stub example CSV file\'' . PHP_EOL;
            File::put($dirPath . DIRECTORY_SEPARATOR . $stubFile, $stubContent);

            $this->info('Stub CSV file created for system "' . $system . '"');
        }
    }
}
