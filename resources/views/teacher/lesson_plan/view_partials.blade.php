<table class="table table-bordered table-sm mb-0">
    <tbody>
        <tr>
            <th class="bg-light" style="width: 200px;">Class</th>
            <td><span class="badge bg-primary">{{ $lessonPlan->schoolClass->name ?? '-' }}</span></td>
        </tr>
        <tr>
            <th class="bg-light">Section</th>
            <td><span class="badge bg-success">{{ $lessonPlan->section->name ?? '-' }}</span></td>
        </tr>
        <tr>
            <th class="bg-light">Subject</th>
            <td><span class="badge bg-info text-dark">{{ $lessonPlan->subject->name ?? '-' }}</span></td>
        </tr>
        <tr>
            <th class="bg-light">Date</th>
            <td><span class="badge bg-warning text-dark">{{ $lessonPlan->date }}</span></td>
        </tr>
        <tr>
            <th class="bg-light">Time</th>
            <td>
                <span class="badge bg-secondary">
                    {{ $lessonPlan->time_from ? \Carbon\Carbon::parse($lessonPlan->time_from)->format('g:i A') : '-' }}
                    To
                    {{ $lessonPlan->time_to ? \Carbon\Carbon::parse($lessonPlan->time_to)->format('g:i A') : '-' }}
                </span>
            </td>
        </tr>
        <tr>
            <th class="bg-light">Lesson</th>
            <td><span class="badge bg-danger">{{ $lessonPlan->lesson->name ?? '-' }}</span></td>
        </tr>
        <tr>
            <th class="bg-light">Topic</th>
            <td><span class="badge" style="background-color:#6610f2;">{{ $lessonPlan->topic->name ?? '-' }}</span></td>
        </tr>
        <tr>
            <th class="bg-light">Sub Topic</th>
            <td><span class="badge" style="background-color:#fd7e14;">{{ $lessonPlan->sub_topic ?? '-' }}</span></td>
        </tr>
        <tr>
            <th class="bg-light">Teaching Method</th>
            <td>{{ $lessonPlan->teaching_method ?? '-' }}</td>
        </tr>
        <tr>
            <th class="bg-light">General Objectives</th>
            <td>{{ $lessonPlan->general_objectives ?? '-' }}</td>
        </tr>
        <tr>
            <th class="bg-light">Previous Knowledge</th>
            <td>{{ $lessonPlan->previous_knowledge ?? '-' }}</td>
        </tr>
        <tr>
            <th class="bg-light">Comprehensive Questions</th>
            <td>{{ $lessonPlan->comprehensive_questions ?? '-' }}</td>
        </tr>
        @if($lessonPlan->youtube_url)
        <tr>
            <th class="bg-light">YouTube URL</th>
            <td><a href="{{ $lessonPlan->youtube_url }}" target="_blank">{{ $lessonPlan->youtube_url }}</a></td>
        </tr>
        @endif
        @if($lessonPlan->lecture_video)
        <tr>
            <th class="bg-light">Lecture Video</th>
            <td>
                <a href="{{ asset('uploads/lesson_plan/' . $lessonPlan->lecture_video) }}" target="_blank">
                    <i class='bx bx-video'></i> View
                </a>
            </td>
        </tr>
        @endif
        @if($lessonPlan->attachment)
        <tr>
            <th class="bg-light">Attachment</th>
            <td>
                <a href="{{ asset('uploads/lesson_plan/' . $lessonPlan->attachment) }}" target="_blank">
                    <i class='bx bx-paperclip'></i> View
                </a>
            </td>
        </tr>
        @endif
        <tr>
            <th class="bg-light">Presentation</th>
            <td>
                <div class="lp-presentation-content">
                    {!! $lessonPlan->presentation ?: '-' !!}
                </div>
            </td>
        </tr>
    </tbody>
</table>

<hr>

{{-- Comments Section --}}
<div class="mt-3">
    <h6 class="mb-2">Comments</h6>

    <div id="lp_comments_list" class="mb-3" style="max-height: 200px; overflow-y: auto;">
        @forelse($lessonPlan->comments as $comment)
        <div class="border-bottom pb-2 mb-2">
            <div class="d-flex justify-content-between">
                <span class="fw-semibold small">{{ $comment->user->name ?? 'Unknown' }}</span>
                <span class="text-muted small">{{ $comment->created_at->diffForHumans() }}</span>
            </div>
            <div class="small">{{ $comment->comment }}</div>
        </div>
        @empty
        <div class="text-muted small">No comments yet.</div>
        @endforelse
    </div>

    <form id="lpCommentForm">
        @csrf
        <input type="hidden" name="lesson_plan_id" value="{{ $lessonPlan->id }}">
        <div class="input-group input-group-sm">
            <input type="text" name="comment" class="form-control" placeholder="Add a comment..." required>
            <button type="submit" class="btn btn-success text-white">
                <i class='bx bx-send'></i>
            </button>
        </div>
    </form>
</div>