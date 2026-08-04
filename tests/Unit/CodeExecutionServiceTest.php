<?php

use App\Services\CodeExecutionService;

$cases = [
    'javascript' => [
        'pass' => <<<'JS'
            const data = JSON.parse(require('fs').readFileSync(0, 'utf8'));
            console.log(JSON.stringify(data.a + data.b));
            JS,
        'fail' => <<<'JS'
            console.log(JSON.stringify(-1));
            JS,
        'crash' => <<<'JS'
            process.exit(1);
            JS,
        'timeout' => <<<'JS'
            setTimeout(() => {}, 10000);
            JS,
    ],
    'php' => [
        'pass' => <<<'PHP'
            <?php
            $data = json_decode(file_get_contents('php://stdin'), true);
            echo json_encode($data['a'] + $data['b']);
            PHP,
        'fail' => <<<'PHP'
            <?php
            echo json_encode(-1);
            PHP,
        'crash' => <<<'PHP'
            <?php
            exit(1);
            PHP,
        'timeout' => <<<'PHP'
            <?php
            sleep(10);
            PHP,
    ],
];

foreach ($cases as $language => $scripts) {
    it("runs a passing {$language} solution against a test case", function () use ($language, $scripts) {
        $service = new CodeExecutionService;

        $results = $service->run($language, $scripts['pass'], [
            ['input' => ['a' => 2, 'b' => 3], 'expected_output' => 5],
        ]);

        expect($results)->toHaveCount(1)
            ->and($results[0]['passed'])->toBeTrue()
            ->and($results[0]['actual_output'])->toBe(5);
    });

    it("reports a failing {$language} solution", function () use ($language, $scripts) {
        $service = new CodeExecutionService;

        $results = $service->run($language, $scripts['fail'], [
            ['input' => ['a' => 2, 'b' => 3], 'expected_output' => 5],
        ]);

        expect($results[0]['passed'])->toBeFalse()
            ->and($results[0]['actual_output'])->toBe(-1);
    });

    it("reports a crashing {$language} solution", function () use ($language, $scripts) {
        $service = new CodeExecutionService;

        $results = $service->run($language, $scripts['crash'], [
            ['input' => null, 'expected_output' => null],
        ]);

        expect($results[0]['passed'])->toBeFalse()
            ->and($results[0]['error'])->not->toBeNull();
    });

    it("reports a timed-out {$language} solution", function () use ($language, $scripts) {
        $service = new CodeExecutionService(timeoutSeconds: 1);

        $results = $service->run($language, $scripts['timeout'], [
            ['input' => null, 'expected_output' => null],
        ]);

        expect($results[0]['passed'])->toBeFalse()
            ->and($results[0]['error'])->toContain('timed out');
    })->group('slow');
}
