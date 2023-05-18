<ul>
    <li class="sidebar-section">
        <div class="sidebar-section-header">{{ucfirst(__('showcase.showcases'))}}</div>
        <div class="sidebar-item"><a href="{{ url(__('showcase.showcases')) }}" class="{{ set_active(__('showcase.showcases')) }}">My {{ucfirst(__('showcase.showcases'))}}</a></div>
        <div class="sidebar-item"><a href="{{ url(__('showcase.showcases').'/'.__('showcase.showcase').'-index') }}" class="{{ set_active(__('showcase.showcases').'/'.__('showcase.showcase').'-index*') }}">All {{ucfirst(__('showcase.showcases'))}}</a></div>
</li>
</ul>