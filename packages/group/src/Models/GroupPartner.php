<?php

namespace Group\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use User\Models\User;

/**
 * @property int $id
 * @property int $group_id
 * @property int $user_id
 * @property int $partner_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class GroupPartner extends Model
{
    use SoftDeletes;

    protected $table = 'group_partners';

    protected $casts = [
        'group_id' => 'int',
        'user_id' => 'int',
        'partner_id' => 'int'
    ];

    /**
     * @return HasOne
     */
    public function partner(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'partner_id');
    }
}
