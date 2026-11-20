{{-- Single institutional logo. Size with height classes from the caller (h-12 w-auto); never stretch or recolor. --}}
<img src="{{ asset('images/logo-ivea-horizontal.png') }}"
    alt="IVEA - Instituto Veracruzano de Educación para los Adultos"
    {{ $attributes->merge(['class' => 'w-auto']) }}>
