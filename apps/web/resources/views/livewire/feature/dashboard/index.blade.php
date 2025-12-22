@php

use App\Enums\UserRoleEnum;

@endphp

<div>
    @switch($role)
        @case(UserRoleEnum::LABORAN->value)
            <livewire:feature.dashboard.lbr-dashboard/>
            @break
        @case(UserRoleEnum::STUDENT->value)
            <livewire:feature.dashboard.student-dashboard/>
            @break
        @default
    @endswitch
</div>
