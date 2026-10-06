<?php

namespace App\Interfaces\Repo\Backend;

use App\Interfaces\EloquentRepositoryInterface;

interface ItemInventoryCsvImportRepo extends EloquentRepositoryInterface
{
    public function parseCsvFile($filePath);
    
    public function validateCsvData($data);
    
    public function importCsvData($data);
}
