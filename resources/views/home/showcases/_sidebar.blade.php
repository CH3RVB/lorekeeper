<ul>
    <li class="sidebar-section">
        <div class="sidebar-section-header">{{ ucfirst(__('showcase.showcases')) }}</div>
        @auth
            @if (Auth::user()->showcases()->count() && Settings::get('user_showcase_limit') == 1)
                <div class="sidebar-item">
                    <a href="{{ url(Auth::user()->showcases()->first()->editUrl) }}" class="{{ set_active(Auth::user()->showcases()->first()->editUrl) }}">My {{ ucfirst(__('showcase.showcase')) }}</a>
                </div>
            @else
                <div class="sidebar-item"><a href="{{ url(__('showcase.showcases')) }}" class="{{ set_active(__('showcase.showcases')) }}">My {{ucfirst(__('showcase.showcases'))}}</a></div>
            @endif
        @endauth
        <div class="sidebar-item">
        <a href="{{ url(__('showcase.showcases') . '/' . __('showcase.showcase') . '-index') }}" class="{{ set_active(__('showcase.showcases') . '/' . __('showcase.showcase') . '-index*') }}">All {{ ucfirst(__('showcase.showcases')) }}</a>
        </div>
    </li>
</ul>