@extends('layouts.app')

@section('content')
<div class="container">

    <div class="row">
        <!-- Chart Section -->
        <div class="col-lg-8">
            <div class="chart-container shadow-sm p-4 rounded bg-white">
                <h5 class="text-secondary text-center mb-3">Students with Projects</h5>
                <canvas id="studentsWithProjectsChart" style="width: 100%; height: 100px;"></canvas>
                <a href="{{ route('coordinator.assigned_projects') }}" class="btn btn-outline-primary btn-sm d-block mt-4 mx-auto" style="width: 150px;">View Details</a>
            </div>
        </div>

        <!-- Cards Section -->
        <div class="col-lg-4">
            <div class="dashboard-card text-center shadow-sm p-4 rounded bg-white mb-4">
                <h5 class="text-secondary">Total Students</h5>
                <p class="dashboard-number display-4 text-primary">{{ $totalStudents }}</p>
                <a href="{{ route('coordinator.students') }}" class="btn btn-outline-primary btn-sm mt-2">View</a>
            </div>
            <div class="dashboard-card text-center shadow-sm p-4 rounded bg-white mb-4">
                <h5 class="text-secondary">Total Supervisors</h5>
                <p class="dashboard-number display-4 text-primary">{{ $totalSupervisors }}</p>
                <a href="#" class="btn btn-outline-primary btn-sm mt-2">View</a>
            </div>
            <div class="dashboard-card text-center shadow-sm p-4 rounded bg-white">
                <h5 class="text-secondary">Total Projects</h5>
                <p class="dashboard-number display-4 text-primary">{{ $totalProjects }}</p>
                <a href="{{ route('coordinator.total_project') }}" class="btn btn-outline-primary btn-sm mt-2">View</a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Data for students with projects chart
        const totalStudents = {{ $totalStudents }};
        const studentsWithProjects = {{ $studentsWithProjects }};

        // Students with Projects Doughnut Chart
        new Chart(document.getElementById('studentsWithProjectsChart'), {
            type: 'doughnut',
            data: {
                labels: ['Students with Projects', 'Students without Projects'],
                datasets: [{
                    data: [studentsWithProjects, totalStudents - studentsWithProjects],
                    backgroundColor: ['#4CAF50', '#E0E0E0'],
                    hoverBackgroundColor: ['#388E3C', '#BDBDBD']
                }]
            },
            options: {
                cutout: '50%',
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            color: '#4B4B4B',
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            }
        });
    });
</script>

<style>
    .dashboard-title {
        font-size: 2rem;
        font-weight: bold;
        color: #333;
    }

    .chart-container {
        background-color: #ffffff;
        border: 1px solid #ddd;
        border-radius: 10px;
    }

    .dashboard-card {
        background-color: #ffffff;
        border: 1px solid #ddd;
        border-radius: 10px;
    }

    .dashboard-number {
        font-size: 2.5rem;
        font-weight: bold;
    }

    .btn-outline-primary {
        font-size: 0.9rem;
    }
</style>
@endsection
