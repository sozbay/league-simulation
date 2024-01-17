<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>League Simulation</title>
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
    <div class="row">
        <div class=" col-md-6">
            <h2>League Table</h2>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th scope="col">Team</th>
                    <th scope="col">Week</th>
                    <th scope="col">Points</th>
                    <th scope="col">Goals For</th>
                    <th scope="col">Goals Against</th>
                </tr>
                </thead>
                <tbody>
                @foreach($teams as $team)
                    <tr>
                        <td>{{ $team->name }}</td>
                        <td>{{ $currentWeek - 1 }}</td>
                        <td>{{ $team->points }}</td>
                        <td>{{ $team->goals_for }}</td>
                        <td>{{ $team->goals_against }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            @if($currentWeek <= $totalWeek)
                <div class="col-md-4 mb-1">
                    <form method="get" action="{{ url('/simulate-week') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">Simulate Next Week</button>
                    </form>
                </div>
            @endif
            @if($currentWeek === 1)
                <div class="col-md-4 mb-1">
                    <form method="get" action="{{ url('/simulate-all-week') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">Simulate All Week</button>
                    </form>
                </div>
            @endif
            <div class="col-md-4">
                <form method="get" action="{{ url('/reset-data') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger">Reset Data</button>
                </form>
            </div>
            @if($currentWeek > 1)
            <div class="row">
                <div class="col-md-12">
                    <h2>Match Results</h2>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th scope="col">Week</th>
                            <th scope="col">Teams and Score</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($matches as $match)

                            <tr>
                                <td>{{$match->week}}. Week</td>
                            <td>{{ $match->homeTeam->name }} {{ $match->home_team_goals }}
                                - {{ $match->away_team_goals }} {{ $match->awayTeam->name}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
        @if($currentWeek > 4)
            <div class="col-md-4">
                <h2>Championship Odds</h2>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Team</th>
                        <th scope="col">Odds</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($championshipOdds as $team => $odds)
                        <tr>
                            <td>{{ $team }}</td>
                            <td>{{ $odds }}%</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif
    </div>
</div>
<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog modal-sm">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" style="text-align: center"><b>The Champion Team</b></h4>
            </div>
            <div class="modal-body" style="text-align: center">
                <img
                    src="https://png.pngtree.com/png-clipart/20230413/original/pngtree-trophy-flat-icon-png-image_9052316.png"
                    height="70">
                <h3><b>{{$teams[0]->name}}</b></h3>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<script type="text/javascript">
    @if($currentWeek-1 == $totalWeek)
    $('#myModal').modal('show');
    @endif
</script>


