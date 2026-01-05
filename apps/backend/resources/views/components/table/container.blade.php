@props([
    'class' => '',
])

<div class=" {{ $class }}">
    <table class="table w-full">
        {{ $slot }}
    </table>
</div>

