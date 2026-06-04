<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteMemberRequest;
use App\Models\User;
use App\Models\Workspace;
use App\Services\WorkspaceMemberService;
use Illuminate\Http\RedirectResponse;

class WorkspaceMemberController extends Controller
{
    public function __construct(
        private readonly WorkspaceMemberService $memberService
    ) {}

    /**
     * Invites an existing user into the workspace.
     */
    public function store(InviteMemberRequest $request, Workspace $workspace): RedirectResponse
    {
        $this->authorize('manageMembers', $workspace);

        try {
            $user = $this->memberService->addMemberByEmail(
                $workspace,
                $request->email,
                $request->user(),
            );

            return redirect()
                ->route('workspaces.show', $workspace)
                ->with('success', "{$user->name} has been added to the workspace.");

        } catch (\RuntimeException $e) {
            return redirect()
                ->route('workspaces.show', $workspace)
                ->withErrors(['invite_email' => $e->getMessage()]);
        }
    }

    /**
     * Removes a member from the workspace.
     */
    public function destroy(Workspace $workspace, User $user): RedirectResponse
    {
        $this->authorize('manageMembers', $workspace);

        try {
            $this->memberService->removeMember($workspace, $user);

            return redirect()
                ->route('workspaces.show', $workspace)
                ->with('success', "{$user->name} has been removed from the workspace.");

        } catch (\RuntimeException $e) {
            return redirect()
                ->route('workspaces.show', $workspace)
                ->withErrors(['member' => $e->getMessage()]);
        }
    }
}
