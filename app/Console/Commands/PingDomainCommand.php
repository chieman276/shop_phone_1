<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Iodev\Whois\Factory as WhoisFactory;
use ReflectionClass;

class PingDomainCommand extends Command
{
    protected $signature = 'domain:ping';
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

    private function getFullObjectData($object)
    {
        $reflection = new ReflectionClass($object);
        $properties = $reflection->getProperties();
        $result = [];

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $result[$property->getName()] = $property->getValue($object);
        }

        return $result;
    }

    private function processDomains(array $domains): array
    {
        $whois = WhoisFactory::get()->createWhois();
        $results = [];
        $i = 1;

        foreach ($domains as $domain) {
            $result = [
                'domain' => $domain,
                'registered_at' => 'N/A',
                'expires_at' => 'N/A',
                'registrar' => 'N/A',
                'owner' => 'N/A',
                'email' => 'N/A',
                'phone' => 'N/A',
                'ip_address' => 'N/A',
                'hosting_provider' => 'N/A',
                'hosting' => 'N/A',
                'email_server_provider' => 'N/A',
            ];

            try {
                print_r('ping_text');
                print_r($i);
                $info = $whois->loadDomainInfo($domain);

                // Xử lý riêng từng trường WHOIS
                if ($info) {

                    if (isset($info->creationDate) || $info->creationDate != null) {
                        $creationDate = date("Y-m-d H:i:s", $info->creationDate);
                        $result['registered_at'] = $creationDate ?? 'N/A';
                    } else {
                        $result['registered_at']  = 'N/A';
                    }
                    if (isset($info->expirationDate) || $info->expirationDate != null) {
                        $expirationDate = date("Y-m-d H:i:s", $info->expirationDate);
                        $result['expires_at'] = $expirationDate ?? 'N/A';
                    } else {
                        $result['expires_at'] = 'N/A';
                    }

                    $result['registrar'] = $info->registrar ?? 'N/A';
                    $result['owner'] = $info->owner ?? 'N/A';

                    $fullInfo = $this->getFullObjectData($info);
                    $result['email'] = $fullInfo['extra']['groups'][0]['Admin Email'] ?? 'N/A';
                    $result['phone'] = $fullInfo['extra']['groups'][0]['Admin Phone'] ?? 'N/A';
                }

                // Lấy IP và xử lý thông tin liên quan
                $result['ip_address'] = gethostbyname($domain);

                // Chỉ gọi khi IP hợp lệ
                if (filter_var($result['ip_address'], FILTER_VALIDATE_IP)) {
                    $result['hosting_provider'] = $this->getHostingProvider($result['ip_address']);
                    $result['hosting'] = $this->getHostingInfoFromAPI($result['ip_address']);
                }

                // Lấy thông tin email server
                $result['email_server_provider'] = $this->getEmailServerProvider($domain);
            } catch (\Exception $e) {
                $this->error("Error processing domain {$domain}: {$e->getMessage()}");
            }
            $i++;
            $results[] = $result;
        }

        return $results;
    }

    private function getHostingProvider(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            $host = gethostbyaddr($ip);
            return $host ?: '';
        }
        return '';
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

    private function getEmailServerProvider(string $domain): string
    {
        $records = dns_get_record($domain, DNS_MX);
        return $records[0]['target'] ?? '';
    }

    private function writeResultsToExcel(string $filePath, array $results): void
    {
        $writer = SimpleExcelWriter::create($filePath);

        foreach ($results as $result) {
            $writer->addRow($result);
        }
    }
}
