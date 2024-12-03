@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-center my-4">Feedback</h1>

    <!-- Toggle Tabs -->
    <ul class="nav nav-tabs" id="feedbackTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="submit-tab" data-bs-toggle="tab" data-bs-target="#submitFeedback" type="button" role="tab" aria-controls="submitFeedback" aria-selected="true">Submit Feedback</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="status-tab" data-bs-toggle="tab" data-bs-target="#feedbackStatus" type="button" role="tab" aria-controls="feedbackStatus" aria-selected="false">Feedback Status</button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content mt-4" id="feedbackTabsContent">
        <!-- Submit Feedback Tab -->
        <div class="tab-pane fade show active" id="submitFeedback" role="tabpanel" aria-labelledby="submit-tab">
            <form action="{{ route('feedback.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" id="subject" name="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}" required>
                    @error('subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea id="message" name="message" class="form-control @error('message') is-invalid @enderror" rows="5" required>{{ old('message') }}</textarea>
                    @error('message')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="screenshot" class="form-label">Screenshot (optional)</label>
                    <input type="file" id="screenshot" name="screenshot" class="form-control @error('screenshot') is-invalid @enderror" accept="image/*">
                    @error('screenshot')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Submit Feedback</button>
            </form>
        </div>

        <!-- Feedback Status Tab -->
        <div class="tab-pane fade" id="feedbackStatus" role="tabpanel" aria-labelledby="status-tab">
            <h4 class="mb-3">Your Feedback Status</h4>
            @if($feedbacks->isEmpty())
                <p class="text-muted">No feedback submitted yet.</p>
            @else
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Screenshot</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($feedbacks as $feedback)
                            <tr>
                                <td>{{ $feedback->subject }}</td>
                                <td>{{ $feedback->message }}</td>
                                <td>
                                    @if($feedback->screenshot)
                                        <a href="{{ asset('storage/' . $feedback->screenshot) }}" target="_blank">View Screenshot</a>
                                    @else
                                        No screenshot
                                    @endif
                                </td>
                                <td>{{ $feedback->status }}</td>
                                <td>{{ $feedback->created_at->format('d-m-Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
