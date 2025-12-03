@props(['class' => null])

<div {{ $attributes->class(['mt-auto w-full', $class]) }} data-flux-sidebar-footer>
    {{ $slot }}
</div>
