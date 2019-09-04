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

    const GZHI_REQUEST_CODE_TYPE = null;
    const GZHI_REQUEST_CODE = null;

    const GZHI_REQUEST_API_VERSION = "1.0.0.5";

    const GJI_SOAP_URL = 'https://next-lk.eiasmo.ru/eds-service/';

    const GZHI_VENDOR_ID = 4;

    const GZHI_STATUSES_LIST = [
        'transferred',
        'transferred_again',
        'accepted',
        'assigned',
        'completed_with_act',
        'completed_without_act',
        'closed_with_confirm',
        'closed_without_confirm',
        'not_verified',
        'waiting',
        'in_process',
        'confirmation_operator',
        'confirmation_client',
        'conflict'
    ];


    public $table = 'gzhi_requests';

    public $timestamps = false;

    public $guarded = [];

    protected $dates = [];


    public function gzhiApiProvider ()
    {
        return $this->hasOne( GzhiApiProvider::class, 'id', 'gzhi_api_provider_id' );
    }

}
