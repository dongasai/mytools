<div>
    APP_DEBUG:  @php dump(env('APP_DEBUG')) @endphp

    config - app :    @php dump(config('app')) @endphp
    config - session :    @php dump(config('session')) @endphp
    config - database :    @php dump(config('database')) @endphp


    <h3> Dev2 </h3>
    {!! $content !!}
</div>
