{{--
    Usage:
    <x-breadcrumb :items="[
        ['label' => 'Students Information', 'url' => '#'],
        ['label' => 'All Students', 'url' => '#'],
    ]" />

    - Har item: ['label' => '...', 'url' => '...']
    - Last item automatically "active" (bina link) ban jata hai, aur wohi breadcrumb-title mein bhi show hota hai
    - Jitne chahein items pass kar sakte hain (2, 3, 4, 5 levels)
    - Agar koi level clickable nahi (sirf group/parent hai), url mein '#' de dein
--}}
@props(['items' => []])

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">
        {{ count($items) ? end($items)['label'] : '' }}
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                </li>

                @foreach ($items as $item)
                    @if ($loop->last)
                        <li class="breadcrumb-item active" aria-current="page">{{ $item['label'] }}</li>
                    @else
                        <li class="breadcrumb-item">
                            <a href="{{ $item['url'] ?? '#' }}">{{ $item['label'] }}</a>
                        </li>
                    @endif
                @endforeach
            </ol>
        </nav>
    </div>
</div>