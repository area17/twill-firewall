<?php

namespace A17\TwillFirewall\Console\Commands;

use Illuminate\Console\Command;
use A17\TwillFirewall\Exceptions\PackageException;
use A17\TwillFirewall\Support\Facades\TwillFirewall;

class ConfigMergeSection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twill-firewall:config:merge {section}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge a section into the published config file';

    /**
     * The section file.
     *
     * @var string
     */
    protected string $sectionFile = '';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Merging section {$this->getSection()} into the published config file...");

        try {
            $this->checkSectionFileExists();

            $this->checkPublishedConfigFileExists();

            $this->checkSectionIsMissinfFromConfig();

            $this->mergeSection();

            $this->info('Merged.');
        } catch (PackageException $e) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    public function checkSectionFileExists(): void
    {
        $section = $this->getSection();

        $file = realpath(TwillFirewall::config('package.path') . "/config/{$section}.php");

        if (is_string($file)) {
            $this->sectionFile = $file;
        }

        $this->throw($message = "The section file '{$this->sectionFile}' does not exist.");
    }

    public function checkPublishedConfigFileExists(): void
    {
        $isPublished = realpath($fileName = config_path(TwillFirewall::packageName() . '.php'));

        if ($isPublished === false) {
            $this->throw(
                "The published config file '{$fileName}' does not exist. Did you forget to publish the config file?",
            );
        }
    }

    /**
     * @throws \Exception
     */
    public function throw(string $message): void
    {
        $this->error($message);

        throw new PackageException($message);
    }

    public function checkSectionIsMissinfFromConfig(): void
    {
        $section = $this->getSection();

        $publishedConfigFile = config_path(TwillFirewall::packageName() . '.php');

        $publishedConfig = require $publishedConfigFile;

        if (isset($publishedConfig[$section])) {
            $this->throw(
                "The section '{$section}' is already present in the published config file '{$publishedConfigFile}'.",
            );
        }
    }

    public function mergeSection(): void
    {
        $file = config_path(TwillFirewall::packageName() . '.php');

        $section = $this->getSection();

        $publishedConfigContents = file_get_contents($file);

        if ($publishedConfigContents === false) {
            $this->throw("Could not read the published config file '{$file}'.");

            return;
        }

        $publishedConfig = require $file;

        if (!is_array($publishedConfig)) {
            $this->throw('The current config file has an error.');
        }

        $newSection = $this->extractArray(file_get_contents($this->sectionFile));

        if (strpos($publishedConfigContents, $endOfArray = "];\n") === false) {
            $this->throw("The published config file array doesn't end properly with '];'.");
        }

        $publishedConfigContents = str_replace(
            $endOfArray,
            "    '{$section}' => {$newSection},\n];\n",
            $publishedConfigContents,
        );

        file_put_contents($updatedFile = "$file.updated", $publishedConfigContents);

        $updated = require $updatedFile;

        if (!is_array($updated)) {
            $this->throw('There was an error trying to update the confi file.');
        }

        foreach ($publishedConfig as $key => $value) {
            if (($updated[$key] ?? null) !== $value) {
                $this->throw('It was not possible to update the config file correctly.');
            }
        }

        file_put_contents($file, $publishedConfigContents);

        unlink($updatedFile);
    }

    public function extractArray(string|false|null $content): string|null
    {
        if ($content === false || $content === null || empty($content)) {
            $this->throw('The section file is empty.');

            return null;
        }

        $lines = explode("\n", $content);

        foreach ($lines as $key => $line) {
            if (blank($line)) {
                continue;
            }

            $line = str_replace('<?php', '', $line);

            $line = str_replace('return', '', $line);

            $line = str_replace(';', '', $line);

            $lines[$key] = '    ' . $line;
        }

        $content = trim($content);

        return trim(implode("\n", $lines));
    }

    public function getSection(): string
    {
        $section = $this->argument('section');

        if (is_string($section)) {
            return $section;
        }

        return '';
    }
}
