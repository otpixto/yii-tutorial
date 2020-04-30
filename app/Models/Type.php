<?php

namespace App\Models;

use App\User;
use Illuminate\Support\Facades\Auth;

class Type extends BaseModel
{

    protected $table = 'types';
    public static $_table = 'types';

    public static $name = 'Классификатор';

    protected $nullable = [
        'guid',
        'color',
        'description',
        'parent_id',
        'group_id',
        'mosreg_id',
    ];

    protected $fillable = [
        'provider_id',
        'name',
        'color',
        'description',
        'parent_id',
        'group_id',
        'period_acceptance',
        'period_execution',
        'season',
        'is_pay',
        'emergency',
        'need_act',
        'works',
        'lk',
        'mosreg_id',
    ];

    public function managements()
    {
        return $this->belongsToMany(Management::class, 'managements_types');
    }

    public function providers()
    {
        return $this->belongsToMany(Provider::class, 'providers_types');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function parent()
    {
        return $this->belongsTo(Type::class);
    }

    public function group()
    {
        return $this->belongsTo(TypeGroup::class);
    }

    public function groups()
    {
        return $this->belongsToMany(TypeGroup::class, 'group_type', 'type_id', 'group_id');
    }

    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'types_vendors')
            ->withPivot(
                'vendor_id',
                'type_id'
            );
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_types')
            ->withPivot(
                'user_id',
                'type_id'
            );
    }

    public function childs()
    {
        return $this->hasMany(Type::class, 'parent_id', 'id');
    }

    public function scopeMine($query, ...$flags)
    {
        if (!in_array(self::IGNORE_PROVIDER, $flags)) {
            $query
                ->where(function ($q) {
                    return $q
                        ->whereNull(self::$_table . '.provider_id')
                        ->orWhereHas('provider', function ($provider) {
                            return $provider
                                ->mine()
                                ->current();
                        });
                });
        }
        if (!Auth::user()
            ->can('tickets.all_types')) {
            $query
                ->whereHas('managements', function ($managements) {
                    $managements->whereHas('users', function ($users) {
                        return $users->where('user_id', Auth::user()->id);
                    });
                    return $managements->mine();
                });
        }
        return $query;
    }

    public function sortByUsersFavoriteTypes(array $types): array
    {
        $user = auth()->user();

        $favoriteTypesList = $user->favorite_types_list;

        if ($favoriteTypesList) {
            $favoriteTypesArray = json_decode($favoriteTypesList);

            $typesArray = [];

            foreach ($favoriteTypesArray as $item) {
                if (array_key_exists($item, $types)) {
                    if (isset($types[$item])) {
                        $typesArray[$item] = $types[$item];
                        unset($types[$item]);
                    }
                }
            }

            $result = [];

            foreach ($typesArray as $key => $value) {
                $result[$key] = $value;
            }

            foreach ($types as $key => $value) {
                $result[$key] = $value;
            }

            $types = $result;
        }
        return $types;
    }

    public function searchData($request)
    {

        $search = trim($request->get('search', ''));
        $parent_id = trim($request->get('parent_id', ''));
        $building_id = trim($request->get('building_id', ''));
        $management_id = trim($request->get('management_id', ''));
        $provider_id = trim($request->get('provider_id', ''));
        $group_id = trim($request->get('group_id', ''));

        $types = self
            ::mine()
            ->select(
                'types.*',
                'parent_type.name AS parent_name'
            )
            ->leftJoin('types AS parent_type', 'parent_type.id', '=', 'types.parent_id')
            ->orderBy(self::$_table . '.name');

        if (!empty($parent_id)) {
            $types
                ->where(function ($q) use ($parent_id) {
                    return $q
                        ->where(self::$_table . '.parent_id', '=', $parent_id)
                        ->orWhere(self::$_table . '.id', '=', $parent_id);
                });
        }

        if (!empty($search)) {
            $s = '%' . str_replace(' ', '%', trim($search)) . '%';
            $types
                ->where(function ($q) use ($s) {
                    return $q
                        ->where(self::$_table . '.name', 'like', $s)
                        ->orWhere(self::$_table . '.guid', 'like', $s)
                        ->orWhere('parent_type.name', 'like', $s)
                        ->orWhere('parent_type.guid', 'like', $s);
                });
        }

        if (!empty($building_id)) {
            $types
                ->whereHas('buildings', function ($buildings) use ($building_id) {
                    return $buildings
                        ->where(Building::$_table . '.id', '=', $building_id);
                });
        }

        if (!empty($management_id)) {
            $types
                ->whereHas('managements', function ($managements) use ($management_id) {
                    return $managements
                        ->where(Management::$_table . '.id', '=', $management_id);
                });
        }

        if (!empty($provider_id)) {
            $types
                ->where(self::$_table . '.provider_id', '=', $provider_id);
        }

        if (!empty($group_id)) {
            $types
                ->where(self::$_table . '.group_id', '=', $group_id);
        }

        $types = $types
            ->with(
                'parent',
                'managements'
            );

        return $types;
    }


    public function getTypeDataArrayForExcel(): array
    {
        $vendorsString = '';
        $j = 0;
        foreach ($this->vendors as $vendor) {
            $j++;
            $vendorsString .= $vendor->id;
            if (count($this->vendors) != $j) {
                $vendorsString .= '|';
            }

        }
        return [
            'id' => $this->id,
            'provider id' => $this->provider_id,
            'parent_id' => $this->parent_id,
            'Категория' => $this->parent ? $this->parent->name : null,
            'Подкатегория (тип)' => $this->name,
            'Период на принятие' => $this->period_acceptance,
            'Период на исполнение' => $this->period_execution,
            'Требуется акт' => $this->need_act,
            'Аварийная' => $this->emergency,
            'Платно' => $this->is_pay,
            'вендор id' => $vendorsString,
            'Mosreg ID' => $this->mosreg_id,
            'Группа id' => $this->group_id,
            'Сезонность' => $this->season,
            'Подсказки' => $this->description
        ];
    }

}
