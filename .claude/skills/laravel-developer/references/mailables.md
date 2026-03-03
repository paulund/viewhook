# Mailables

## Always Implement `ShouldQueue`

Every mailable class must implement `ShouldQueue`. This ensures the mail is dispatched asynchronously via the queue, keeping HTTP responses fast.

```php
// ✅ CORRECT
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;

final class TeamInvitationMail extends Mailable implements ShouldQueue
{
    // ...
}

// ❌ WRONG — blocks the HTTP request during SMTP handshake
final class TeamInvitationMail extends Mailable
{
    // ...
}
```

## Use `->send()`, Not `->queue()`

When a mailable implements `ShouldQueue`, calling `Mail::to($email)->send(...)` automatically queues it. Do **not** call `->queue()` explicitly — it is redundant and bypasses features like `ShouldQueue` configuration.

```php
// ✅ CORRECT — auto-queued because TeamInvitationMail implements ShouldQueue
Mail::to($email)->send(new TeamInvitationMail($invitation));

// ❌ WRONG — explicit queue() call is unnecessary
Mail::to($email)->queue(new TeamInvitationMail($invitation));
```

## Test with `assertQueued` / `assertNotQueued`

When `Mail::fake()` is active, use `assertQueued` and `assertNotQueued` — not `assertSent` / `assertNotSent`.

```php
// ✅ CORRECT — matches ShouldQueue behaviour
Mail::fake();
// ... trigger the action ...
Mail::assertQueued(TeamInvitationMail::class, fn ($mail) => $mail->hasTo('user@example.com'));
Mail::assertNotQueued(TeamInvitationMail::class);

// ❌ WRONG — assertSent will always fail for queued mailables
Mail::assertSent(TeamInvitationMail::class);
Mail::assertNotSent(TeamInvitationMail::class);
```

## Rendering Mail Content in Tests

To exercise the `content()` method (view rendering), send without `Mail::fake()` using the `array` mail driver (configured automatically in the test environment). No assertion is needed — a successful response confirms the mailable rendered without errors.

```php
it('sends invitation with rendered mail content', function (): void {
    // No Mail::fake() — the array driver renders the Mailable
    $owner = $this->createBusinessUser();

    $response = $this->actingAs($owner)->post(route('settings.team.invitations.store'), [
        'email' => 'rendered@example.com',
    ]);

    $response->assertRedirect(route('settings.team'));
});
```
