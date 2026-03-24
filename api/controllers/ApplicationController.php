<?php
require_once __DIR__ . '/../lib/DB.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../middleware/auth.php';

class ApplicationController {
    private const AI_LIMITS = ['free' => 3, 'basic' => 20, 'pro' => 9999];

    public static function index(): void {
        $user = requireAuth();
        $apps = DB::findAll(
            'SELECT * FROM applications WHERE user_id = ? ORDER BY created_at DESC',
            [$user['id']]
        );
        Response::json($apps);
    }

    public static function store(): void {
        $user  = requireAuth();
        $body  = json_decode(file_get_contents('php://input'), true) ?? [];
        $limit = self::AI_LIMITS[$user['plan']] ?? 3;

        if ((int) $user['ai_used'] >= $limit) {
            Response::error('AI limit reached. Please upgrade your plan.', 403);
        }

        $jobTitle = trim($body['job_title'] ?? '');
        $company  = trim($body['company']   ?? '');
        $jobUrl   = trim($body['job_url']   ?? '');
        $status   =      $body['status']    ?? 'applied';

        if (!$jobTitle) Response::error('job_title is required');

        $id = DB::insert(
            'INSERT INTO applications (user_id, job_title, company, job_url, status) VALUES (?, ?, ?, ?, ?) RETURNING id',
            [$user['id'], $jobTitle, $company, $jobUrl, $status]
        );

        DB::query('UPDATE users SET ai_used = ai_used + 1 WHERE id = ?', [$user['id']]);

        $app = DB::find('SELECT * FROM applications WHERE id = ?', [$id]);
        Response::json($app, 201);
    }

    public static function destroy(int $id): void {
        $user = requireAuth();
        $app  = DB::find('SELECT id FROM applications WHERE id = ? AND user_id = ?', [$id, $user['id']]);
        if (!$app) Response::error('Not found', 404);

        DB::query('DELETE FROM applications WHERE id = ?', [$id]);
        Response::json(['message' => 'Deleted']);
    }
}
