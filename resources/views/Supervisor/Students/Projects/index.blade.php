@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Projects & Students Under Supervision</h1>

    <!-- Toggle Tabs -->
    <ul class="nav nav-tabs" id="supervisorTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="projects-tab" data-bs-toggle="tab" data-bs-target="#projects" type="button" role="tab" aria-controls="projects" aria-selected="true">
                Projects & Students
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="leaderboard-tab" data-bs-toggle="tab" data-bs-target="#leaderboard" type="button" role="tab" aria-controls="leaderboard" aria-selected="false">
                Leaderboard
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content mt-3" id="supervisorTabsContent">
        <!-- Projects & Students Tab -->
        <div class="tab-pane fade show active" id="projects" role="tabpanel" aria-labelledby="projects-tab">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Students</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $project)
                        <tr>
                            <td>{{ $project->title }}</td>
                            <td>
                                @foreach ($project->students as $student)
                                    <div>{{ $student->name }}</div>
                                @endforeach
                            </td>
                            <td>
                                @if ($project->students->isNotEmpty())
                                    <a href="{{ route('supervisor.students.projects.view_student_project', $project->students->first()->id) }}" class="btn btn-primary btn-sm">View Progress</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Leaderboard Tab -->
        <div class="tab-pane fade" id="leaderboard" role="tabpanel" aria-labelledby="leaderboard-tab">
    <h3 class="mb-4 text-center">🏆 Supervisor's Project Leaderboard (Points-Based) 🏆</h3>
    <table class="table table-bordered">
        <thead>
            <tr class="text-center">
                <th>Rank</th>
                <th>Medal</th>
                <th>Project Title</th>
                <th>Students</th>
                <th>Points</th>
                <th>Progress</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Calculate leaderboard based on points
                $leaderboard = $projects->map(function ($project) {
                    $totalTasks = $project->tasks->count();
                    $completedTasks = $project->tasks->where('status', 'completed')->count();
                    $overdueTasks = $project->tasks->where('status', 'overdue')->count();
                    $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

                    // Points calculation
                    $points = ($completedTasks * 10); // 10 points per completed task
                    if ($progress === 100) {
                        $points += 50; // Bonus for fully completed projects
                    }
                    $points -= ($overdueTasks * 2); // Penalty for overdue tasks

                    return [
                        'title' => $project->title,
                        'students' => $project->students->pluck('name')->toArray(),
                        'progress' => $progress,
                        'points' => $points,
                    ];
                })->sortByDesc('points')->values(); // Sort projects by points
            @endphp

            @forelse ($leaderboard as $index => $project)
                <tr class="text-center">
                    <!-- Rank -->
                    <td>{{ $index + 1 }}</td>
                    
                    <!-- Medal -->
                    <td>
                        @if ($index == 0)
                            🥇 <!-- Gold Medal -->
                        @elseif ($index == 1)
                            🥈 <!-- Silver Medal -->
                        @elseif ($index == 2)
                            🥉 <!-- Bronze Medal -->
                        @else
                            🎖️ <!-- Participation Medal -->
                        @endif
                    </td>

                    <!-- Project Title -->
                    <td>{{ $project['title'] }}</td>

                    <!-- Students -->
                    <td>
                        @foreach ($project['students'] as $student)
                            <div>{{ $student }}</div>
                        @endforeach
                    </td>

                    <!-- Points -->
                    <td>{{ $project['points'] }} pts</td>

                    <!-- Progress with badges -->
                    <td>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar 
                                @if ($project['progress'] >= 75) bg-success
                                @elseif ($project['progress'] >= 50) bg-info
                                @elseif ($project['progress'] >= 25) bg-warning
                                @else bg-danger
                                @endif"
                                role="progressbar"
                                style="width: {{ $project['progress'] }}%;"
                                aria-valuenow="{{ $project['progress'] }}"
                                aria-valuemin="0" aria-valuemax="100">
                                {{ $project['progress'] }}%
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No projects available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

    </div>
</div>

<style>
    .progress-bar {
    transition: width 0.5s ease;
    font-size: 14px;
    font-weight: bold;
    color: white;
}

.table td, .table th {
    vertical-align: middle;
}

h3.text-center {
    color: #6c757d; /* Muted color for title */
    font-weight: bold;
}

    .progress-bar[aria-valuenow="100"] {
        background-color: #28a745; /* Green for 100% */
    }

    .progress-bar[aria-valuenow="50"] {
        background-color: #ffc107; /* Yellow for mid-progress */
    }

    .progress-bar[aria-valuenow="0"] {
        background-color: #dc3545; /* Red for no progress */
    }
</style>
@endsection



