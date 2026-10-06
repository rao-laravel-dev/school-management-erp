{{-- Add Room Modal --}}
<div class="modal fade room-modal" id="roomAdd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('rooms.store') }}" method="POST" class="room-form" novalidate>
                @csrf
                <input type="hidden" name="_modal" value="roomAdd">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white">Add Room</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('admin.rooms.fields', ['p' => 'add', 'room' => null, 'f' => old('_modal') === 'roomAdd'])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success btn-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Room Modals (one per row) --}}
@foreach($rooms as $room)
<div class="modal fade room-modal" id="roomEdit-{{ $room->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('rooms.update', $room->id) }}" method="POST" class="room-form" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="roomEdit-{{ $room->id }}">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('admin.rooms.fields', ['p' => 'edit_' . $room->id, 'room' => $room, 'f' => old('_modal') === 'roomEdit-' . $room->id])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning btn-sm">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
