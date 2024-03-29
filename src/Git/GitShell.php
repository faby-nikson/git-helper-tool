<?php

namespace FDTool\GitChecker\Git;

class GitShell
{
    public static function checkCurrentBranch(string $gitProjectPath, string $branchToCompare = 'master'): void
    {
        $result = trim(
            shell_exec(
                sprintf("cd %s && (git branch | grep -F '*' | awk '{print $2}')", $gitProjectPath)
            )
        );
        if($result !== $branchToCompare) {
            throw new \RuntimeException($result);
        }
    }

    public static function hasModifiedFiles(string $gitProjectPath): bool
    {
        $result = trim(
            shell_exec(
                sprintf("cd %s && git status | grep modif", $gitProjectPath)
            )
        );
        return !empty($result);
    }

    public static function hasAddedFiles(string $gitProjectPath): bool
    {
        $result = trim(
            shell_exec(
                sprintf("cd %s && git status | grep new", $gitProjectPath) //  "nouveau"
            )
        );
        return !empty($result);
    }

    public static function hasDeletedFiles(string $gitProjectPath): bool
    {
        $result = trim(
            shell_exec(
                sprintf("cd %s && git status | grep Removed", $gitProjectPath) //  "supprimé"
            )
        );
        return !empty($result);
    }

    public static function hasUntrackedFiles($gitProjectPath): bool
    {
        $result = trim(
            shell_exec(
                sprintf("cd %s && git status | grep untracked", $gitProjectPath) //  "non suivis"
            )
        );
        return !empty($result);
    }

    public static function executeGitCleanIgnoredFiles(): ?string
    {
        return shell_exec("git clean -Xf");
    }

    public static function executeGitCleanUntrackedFiles(): ?string
    {
        return shell_exec("git clean -df");
    }

    public static function removeMergedBranches(): ?string
    {
        $branchesInline = trim(shell_exec("git branch --merged | egrep -v '(^\*|master)'"));
        $result = '';

        if(empty($branchesInline)) {
            return 'Nothing to remove';
        }

        foreach (explode("\n", trim($branchesInline)) as $branchToRemove) {
            // Clean local branches that have already been merged to master
            $result .= $branchToRemove."\n";
            $result .= shell_exec(sprintf("git branch -d %s", $branchToRemove));
        }
        // Then we clean the repository
        $result .= shell_exec("git fetch --prune");

        return $result;
    }
}
