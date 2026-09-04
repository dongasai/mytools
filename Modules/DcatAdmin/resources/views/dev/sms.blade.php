<ul>
    @empty($list)
        没有数据
    @endempty

    @foreach ($list as $sms)
        <li>
            手机 {{ $sms[0] }} 验证码 {{ $sms[1] }}
        </li>

    @endforeach

</ul>
