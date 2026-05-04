<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ClientCategory;
use App\Models\ClientSubCategory;
use App\Models\UseTypes;

class Clients extends Model {
    use HasFactory;

    protected $table = 'clients';
    protected $fillable = [
        'client_acc_ID', 'commercialName', 'legalName', 'nit', 'categoryID', 'subcategoryID', 'useTypes', 'website_link', 'client_status', 'annotations', 'judicialNotificationAddress', 'judicialNotification', 'companySize', 'companyType', 'annualIncome', 'licenseInformation', 'licenseAttachement', 'bankName', 'bankAccountNumber', 'bankCode', 'documentRepository', 'personalContactData', 'happenClient'
    ];

    public function mainCategory() {
        return $this->belongsTo(ClientCategory::class, 'categoryID');
    }

    public function subCategory() {
        return $this->belongsTo(ClientSubCategory::class, 'subcategoryID');
    }

    public function useTypesRecord() {
        return $this->belongsTo(UseTypes::class, 'useTypes');
    }

    public function basicInfoStatus() {
        return $this->belongsTo(BasicInfoStatus::class, 'client_status');
    }
}
