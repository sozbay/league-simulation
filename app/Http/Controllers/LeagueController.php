<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Matches;
use Illuminate\Support\Facades\Log;
use function Psy\sh;

class LeagueController extends Controller
{
    public function showLeague()
    {
        $teams = Team::orderBy('points', 'desc')->orderByRaw('(goals_for - goals_against) DESC')->get();
        $teamCount = Team::query()->count();
        $matches = Matches::query()->with(['homeTeam', 'awayTeam'])->get();
        $currentWeek = $this->getCurrentWeek();
        $championshipOdds = $this->calculateChampionshipOdds();
        $totalWeek = (($teamCount - 1) * 2);

        return view('league', compact('teams', 'matches', 'currentWeek', 'championshipOdds','totalWeek'));
    }

    public function resetData()
    {
        Matches::query()->truncate();

        Team::query()->update([
            'points' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
        ]);

        session(['current_week' => 1]);

        return redirect('/league');
    }

    private function getCurrentWeek()
    {
        return session('current_week', 1);
    }

    public function simulateWeek()
    {
        $this->simulateMatches();
        $this->updateLeagueTable();

        return redirect('/league');
    }

    public function simulateAllWeek()
    {
        $teamCount = Team::query()->count();

        for ($i = 0; $i < (($teamCount - 1) * 2); $i++) {
            $this->simulateMatches();
            $this->updateLeagueTable();
        }

        return redirect('/league');
    }

    private function simulateMatches()
    {
        $teams = Team::all();
        $currentWeek = $this->getCurrentWeek();
        $teams = $teams->shuffle();

        foreach ($teams as $homeTeam) {
            $awayTeam = $this->getOpponentTeam($homeTeam, $currentWeek);
            if ($awayTeam) {
                $teamChange = $this->changeTeams($homeTeam, $awayTeam);
                $this->simulateSingleMatch($teamChange['team1'], $teamChange['team2'], $currentWeek);
            }
        }

        session(['current_week' => $currentWeek + 1]);
    }

    private function getOpponentTeam($homeTeam, $currentWeek)
    {
        $playedMatches = Matches::where('week', $currentWeek)
            ->pluck('home_team_id')
            ->merge(Matches::where('week', $currentWeek)->pluck('away_team_id'));

        $availableTeams = Team::whereNotIn('id', $playedMatches)
            ->where('id', '!=', $homeTeam->id)
            ->get();

        if ($availableTeams->count() > 0 && !$playedMatches->contains($homeTeam->id)) {
            $lastWeekMatch = Matches::where(function ($query) use ($homeTeam) {
                $query->where('home_team_id', $homeTeam->id)
                    ->orWhere('away_team_id', $homeTeam->id);
            })
                ->where('week', $currentWeek - 1)
                ->where('played', false)
                ->first();

            if (!$lastWeekMatch || $lastWeekMatch->home_team_id != $homeTeam->id) {
                $awayTeam = $availableTeams->random();
                if ($this->hasTeamPlayedMatchCount($homeTeam, $awayTeam) === 2) {
                    return $this->getOpponentTeam($homeTeam, $currentWeek);
                }

                return $awayTeam;
            }
        }

        return null;
    }

    /**
     * @param $homeTeam
     * @param $awayTeam
     * @return array
     */
    private function changeTeams($homeTeam, $awayTeam): array
    {
        $exists = Matches::query()
            ->where('home_team_id', $homeTeam->id)
            ->where('away_team_id', $awayTeam->id)
            ->exists();

        if ($exists) {
            return [
                'team1' => $awayTeam,
                'team2' => $homeTeam
            ];
        }

        return [
            'team1' => $homeTeam,
            'team2' => $awayTeam
        ];
    }

    private function hasTeamPlayedMatchCount($team1, $team2)
    {
        return Matches::where(function ($query) use ($team1, $team2) {
            $query->where('home_team_id', $team1->id)->where('away_team_id', $team2->id);
        })->orWhere(function ($query) use ($team1, $team2) {
            $query->where('home_team_id', $team2->id)->where('away_team_id', $team1->id);
        })->count();
    }

    private function simulateSingleMatch($homeTeam, $awayTeam, $currentWeek)
    {
        $homeScore = $this->calculateMatchScore($homeTeam->strength, $homeTeam->fan_power, $awayTeam->strength);
        $awayScore = $this->calculateMatchScore($awayTeam->strength, 1, $homeTeam->strength);

        Matches::create([
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'home_team_goals' => $homeScore,
            'away_team_goals' => $awayScore,
            'week' => $currentWeek,
            'played' => true
        ]);
    }

    private function updateLeagueTable()
    {
        $teams = Team::all();

        foreach ($teams as $team) {
            $team->points = 0;
            $team->goals_for = 0;
            $team->goals_against = 0;

            $homeMatches = Matches::where('home_team_id', $team->id)->get();
            $awayMatches = Matches::where('away_team_id', $team->id)->get();

            foreach ($homeMatches as $match) {
                $team->goals_for += $match->home_team_goals;
                $team->goals_against += $match->away_team_goals;

                if ($match->home_team_goals > $match->away_team_goals) {
                    $team->points += 3;
                } elseif ($match->home_team_goals == $match->away_team_goals) {
                    $team->points += 1;
                }
            }

            foreach ($awayMatches as $match) {
                $team->goals_for += $match->away_team_goals;
                $team->goals_against += $match->home_team_goals;

                if ($match->away_team_goals > $match->home_team_goals) {
                    $team->points += 3;
                } elseif ($match->away_team_goals == $match->home_team_goals) {
                    $team->points += 1;
                }
            }

            $team->save();
        }
    }

    private function calculateMatchScore($teamStrength, $fanPower, $opponentStrength)
    {
        $baseScore = rand(1, 7);
        $scoreChange = (($teamStrength + $fanPower) * 0.4 - ($opponentStrength) * 0.2) / 2;

        return rand(1, min(7, $baseScore + $scoreChange));
    }

    private function calculateChampionshipOdds()
    {
        $teams = Team::orderBy('points', 'desc')->orderByRaw('(goals_for - goals_against) DESC')->get();
        $totalPoints = $teams->sum('points');

        $championshipOdds = [];

        if ($this->getCurrentWeek() > 1) {
            foreach ($teams as $team) {
                $odds = ($team->points / $totalPoints) * 100;
                $championshipOdds[$team->name] = round($odds, 2);
            }
        }

        return $championshipOdds;
    }
}
