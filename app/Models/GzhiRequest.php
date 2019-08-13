<?php

namespace App\Models;

class GzhiRequest extends BaseModel
{
    const GZHI_REQUEST_STATUS_IN_WORK = 0;
    const GZHI_REQUEST_STATUS_COMPLETE = 1;
    const GZHI_REQUEST_STATUS_ERROR = 2;

    const GZHI_REQUEST_TRANSPORT_STATUS_SUCCESS = "110";

    const GZHI_REQUEST_IMPORT_METHOD = "ImportAppealData";
    const GZHI_REQUEST_GET_STATE_METHOD = "GetStateDS";

    const GZHI_REQUEST_STATUS_REGISTERED = 30;

    const GZHI_REQUEST_MAX_ATTEMPTS_COUNT = 30;

    const GZHI_REQUEST_CODE_TYPE = 36;
    const GZHI_REQUEST_CODE = 36001;

    const GZHI_REQUEST_API_VERSION = "1.0.0.5";

    const GJI_SOAP_URL = 'https://lk.eiasmo.ru/eds-service/';


    public $table = 'gzhi_requests';

    public $timestamps = false;

    public $guarded = [];

    protected $dates = [];


    public function gzhiApiProvider ()
    {
        return $this->hasOne( GzhiApiProvider::class, 'id', 'gzhi_api_provider_id' );
    }

}