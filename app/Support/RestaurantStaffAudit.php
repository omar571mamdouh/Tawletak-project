<?php

namespace App\Support;

use App\Models\RestaurantStaff;
use App\Models\RestaurantStaffAuditLog;

class RestaurantStaffAudit
{
    // تستخدم auth('staff') (بعد ما يبقى user authenticated)
    public static function log(
        string $action,
        ?object $entity = null,
        array $meta = [],
        ?array $before = null,
        ?array $after = null,
        ?int $statusCode = null
    ): void {
        $staff = auth('staff')->user();
        if (!$staff) return;

        $branchId = $staff->branch_id ?? ($entity->branch_id ?? null);

        RestaurantStaffAuditLog::create([
            'restaurant_id' => $staff->restaurant_id,
            'branch_id'     => $branchId,
            'staff_id'      => $staff->id,

            'action'        => $action,
            'entity_type'   => $entity ? class_basename($entity) : null,
            'entity_id'     => $entity->id ?? null,

            'method'        => request()?->method(),
            'path'          => request()?->path(),
            'status_code'   => $statusCode,
            'ip'            => request()?->ip(),
            'user_agent'    => request()?->userAgent(),

            'meta'          => $meta ?: null,
            'before'        => $before,
            'after'         => $after,
        ]);
    }

    // تستخدم staff مباشرة (ممتازة للـ login والـ failed login)
    public static function logForStaff(
        RestaurantStaff $staff,
        string $action,
        array $meta = [],
        ?array $before = null,
        ?array $after = null,
        ?int $statusCode = null
    ): void {
        RestaurantStaffAuditLog::create([
            'restaurant_id' => $staff->restaurant_id,
            'branch_id'     => $staff->branch_id,
            'staff_id'      => $staff->id,

            'action'        => $action,
            'entity_type'   => class_basename($staff),
            'entity_id'     => $staff->id,

            'method'        => request()?->method(),
            'path'          => request()?->path(),
            'status_code'   => $statusCode,
            'ip'            => request()?->ip(),
            'user_agent'    => request()?->userAgent(),

            'meta'          => $meta ?: null,
            'before'        => $before,
            'after'         => $after,
        ]);
    }
}
