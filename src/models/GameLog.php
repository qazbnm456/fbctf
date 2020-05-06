<?hh // strict

class GameLog extends Model {
  private function __construct(
    private string $ts,
    private string $entry,
    private int $team_id,
    private int $level_id,
    private int $points,
    private string $type,
    private string $flag,
    private string $client_ip,
  ) {}

  public function getTs(): string {
    return $this->ts;
  }

  public function getEntry(): string {
    return $this->entry;
  }

  public function getTeamId(): int {
    return $this->team_id;
  }

  public function getLevelId(): int {
    return $this->level_id;
  }

  public function getPoints(): int {
    return $this->points;
  }

  public function getType(): string {
    return $this->type;
  }

  public function getFlag(): string {
    return $this->flag;
  }

  public function getClientIp(): string {
    return $this->client_ip;
  }

  private static function gamelogFromRow(Map<string, string> $row): GameLog {
    return new GameLog(
      must_have_idx($row, 'ts'),
      must_have_idx($row, 'entry'),
      intval(must_have_idx($row, 'team_id')),
      intval(must_have_idx($row, 'level_id')),
      intval(must_have_idx($row, 'points')),
      must_have_idx($row, 'type'),
      must_have_idx($row, 'flag'),
      must_have_idx($row, 'client_ip'),
    );
  }

  // Get all game scores.
  public static async function genGameLog(): Awaitable<array<GameLog>> {
    $db = await self::genDb();
    $result =
      await $db->queryf(
        'SELECT ts, %s AS entry, team_id, level_id, points, type, %s AS flag, client_ip FROM scores_log UNION SELECT ts, %s AS entry, team_id, level_id, 0 AS points, %s AS type, flag, client_ip FROM failures_log ORDER BY ts DESC',
        'score',
        '',
        'failure',
        '',
      );

    $gamelog = array();
    foreach ($result->mapRows() as $row) {
      $gamelog[] = self::gamelogFromRow($row);
    }

    return $gamelog;
  }
}
