{!! strip_tags(isset($header) ? $header : '') !!}

{!! strip_tags($slot) !!}
@isset($subcopy)

{!! strip_tags($subcopy) !!}
@endisset

{!! strip_tags(isset($footer) ? $footer : '') !!}
