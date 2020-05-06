<?hh // strict

class FailureLog extends Model {
  private function __construct(
    private int $id,
    private string $ts,
    private int $team_id,
    private int $level_id,
    private string $flag,
    private string $client_ip,
  ) {}

  public function getId(): int {
    return $this->id;
  }

  public function getTs(): string {
    return $this->ts;
  }

  public function getTeamId(): int {
    return $this->team_id;
  }

  public function getLevelId(): int {
    return $this->level_id;
  }

  public function getFlag(): string {
    return $this->flag;
  }

  public function getClientIp(): string {
    return $this->client_ip;
  }

  private static function failurelogFromRow(
    Map<string, string> $row,
  ): FailureLog {
    return new FailureLog(
      intval(must_have_idx($row, 'id')),
      must_have_idx($row, 'ts'),
      intval(must_have_idx($row, 'team_id')),
      intval(must_have_idx($row, 'level_id')),
      must_have_idx($row, 'flag'),
      must_have_idx($row, 'client_ip'),
    );
  }

  // Log attempt on score.
  public static async function genLogFailedScore(
    int $level_id,
    int $team_id,
    string $flag,
  ): Awaitable<void> {
    $client_ip = SessionUtils::sessionClientIp();
    $db = await self::genDb();
    await $db->queryf(
      'INSERT INTO failures_log (ts, level_id, team_id, flag, client_ip) VALUES(NOW(), %d, %d, %s, %s)',
      $level_id,
      $team_id,
      $flag,
      $client_ip,
    );
  }

  // Reset all failures.
  public static async function genResetFailures(): Awaitable<void> {
    $db = await self::genDb();
    await $db->queryf('DELETE FROM failures_log WHERE id > 0');
  }

  // Get all scores.
  public static async function genAllFailures(): Awaitable<array<FailureLog>> {
    $db = await self::genDb();
    $result =
      await $db->queryf('SELECT * FROM failures_log ORDER BY ts DESC');

    $failures = array();
    foreach ($result->mapRows() as $row) {
      $failures[] = self::failurelogFromRow($row);
    }

    return $failures;
  }

  // Get all scores by team.
  public static async function genAllFailuresByTeam(
    int $team_id,
  ): Awaitable<array<FailureLog>> {
    $db = await self::genDb();
    $result = await $db->queryf(
      'SELECT * FROM failures_log WHERE team_id = %d ORDER BY ts DESC',
      $team_id,
    );

    $failures = array();
    foreach ($result->mapRows() as $row) {
      $failures[] = self::failurelogFromRow($row);
    }

    return $failures;
  }
}
