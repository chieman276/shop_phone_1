<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class FetchDomainInfo extends Command
{
    protected $signature = 'domain:fetch';
    protected $description = 'Ping domains to fetch IP address';

    public function handle()
    {
        $inputPath = storage_path('app/ping_domain/lan_1.xlsx');
        $outputPath = storage_path('app/output_file_lan_1.xlsx');

        // Load Excel file
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $outputData = [['Domain', 'IP Address', 'Status']];

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Skip header

            $domain = $row[0] ?? null;
            if (!$domain) continue;

            try {
                $process = new Process(["ping", "-c", "1", $domain]);
                $process->run();

                if ($process->isSuccessful()) {
                    $ipAddress = gethostbyname($domain);
                    $outputData[] = [$domain, $ipAddress, 'Reachable'];
                } else {
                    $outputData[] = [$domain, 'N/A', 'Unreachable'];
                }
            } catch (\Exception $e) {
                $outputData[] = [$domain, 'Error', 'Error'];
            }
        }

        // Save to output Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($outputData);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($outputPath);

        $this->info("Ping results saved to {$outputPath}");
    }
}
