<?php

declare(strict_types=1);

namespace Ttskch\JpPostalCodeApi\DataSource;

use League\Csv\Reader;
use Ttskch\JpPostalCodeApi\Exception\RuntimeException;

final readonly class CsvProvider implements CsvProviderInterface
{
    public function fromZipUrl(string $zipUrl, string $csvEncoding = 'Shift_JIS'): Reader
    {
        $zipFilePath = tempnam(sys_get_temp_dir(), md5(uniqid())).'.zip';
        $csvDirPath = sys_get_temp_dir().'/'.md5(uniqid());

        $zipContent = file_get_contents($zipUrl);
        file_put_contents($zipFilePath, $zipContent);

        $zip = new \ZipArchive();

        if (true === $zip->open($zipFilePath)) {
            $csvFileName = $zip->getNameIndex(0);
            $csvFilePath = $csvDirPath.'/'.$csvFileName;

            $zip->extractTo($csvDirPath);
            $zip->close();

            if ('UTF-8' !== $csvEncoding) {
                if (($content = file_get_contents($csvFilePath)) !== false) {
                    $content = mb_convert_encoding($content, 'UTF-8', $csvEncoding);
                    file_put_contents($csvFilePath, $content);
                }
            }

            $csv = Reader::createFromPath($csvFilePath);

            return $csv;
        }

        throw new RuntimeException('Failed to open zip file.');
    }
}
