@props([
    'title',
    'breadcrumbs',
])

<div {{ $attributes->merge(['class' => '']) }}>
    <x-page.title
        :title="$title"
    />

    <x-page.breadcrumbs
        :items="$breadcrumbs"
    />
</div>
