<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Iodev\Whois\Factory as WhoisFactory;
use ReflectionClass;

class ProcessDomains extends Command
{
    protected $signature = 'process:domains';
    protected $description = 'Ping domains and output WHOIS & hosting information to Excel';

    public function handle()
    {
        $inputFile = storage_path('app/ping_domain/ping_text.xlsx');
        $outputFile = storage_path('app/output_file_hosting_ping_text.xlsx');

        if (!file_exists($inputFile)) {
            $this->error("Input file not found at: $inputFile");
            return;
        }

        $domains = $this->readDomainsFromExcel($inputFile);
        $results = $this->processDomains($domains);
        $this->writeResultsToExcel($outputFile, $results);

        $this->info("Output saved to: $outputFile");
    }

    private function readDomainsFromExcel(string $filePath): array
    {
        $data = [];
        $rows = \Spatie\SimpleExcel\SimpleExcelReader::create($filePath)->getRows();
        foreach ($rows as $row) {
            $data[] = $row['domain'] ?? null;
        }
        return array_filter($data);
    }

    private function processDomains(array $domains): array
    {
        $whois = WhoisFactory::get()->createWhois();
        $results = [];
        $i = 1;

        foreach ($domains as $domain) {
            $result = [
                'domain' => $domain,
                'hosting' => 'N/A',
            ];

            try {
                print_r('ping_text');
                print_r($i);
                $result['ip_address'] = gethostbyname($domain);
                if (filter_var($result['ip_address'], FILTER_VALIDATE_IP)) {
                    $result['hosting'] = $this->getHostingInfoFromAPI($result['ip_address']);
                }
            } catch (\Exception $e) {
                $this->error("Error processing domain {$domain}: {$e->getMessage()}");
            }
            $i++;
            $results[] = $result;
        }

        return $results;
    }

    private function getHostingInfoFromAPI(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            try {
                // Sử dụng một API như IPinfo.io để lấy thông tin hosting
                $response = file_get_contents("https://ipinfo.io/{$ip}/json");
                $data = json_decode($response, true);

                return $data['org'] ?? ''; // Trả về tên tổ chức hoặc ISP
            } catch (\Exception $e) {
                $this->error("Error fetching hosting info for IP {$ip}: {$e->getMessage()}");
            }
        }
        return '';
    }


    private function writeResultsToExcel(string $filePath, array $results): void
    {
        $writer = SimpleExcelWriter::create($filePath);

        foreach ($results as $result) {
            $writer->addRow($result);
        }
    }
}
