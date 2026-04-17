# YouTube Integration

Lifstories Broadcast can optionally connect to a YouTube channel and create a scheduled YouTube Live event from an existing diffusion.

This does not replace YouTube Live, YouTube Studio, OBS, or the client's encoder workflow. It creates the YouTube Live event, saves the returned video ID, and lets the existing public page embed that live automatically.

## Requirements

- A Google account with access to the client's YouTube channel.
- A Google Cloud project.
- YouTube Data API v3 enabled.
- An OAuth Client ID of type Web application.
- The channel must be eligible for YouTube Live.

## Google Cloud Setup

1. Open Google Cloud Console.
2. Create or select a project.
3. Enable `YouTube Data API v3`.
4. Configure the OAuth consent screen.
5. Create an OAuth Client ID with type `Web application`.
6. Add the redirect URI shown in the admin YouTube page.

The redirect URI usually looks like:

```text
https://yourdomain.com/lifestories-broadcast/admin/youtube/callback.php
```

## Config

Edit `config/config.php`:

```php
'youtube' => [
    'client_id' => 'GOOGLE_CLIENT_ID',
    'client_secret' => 'GOOGLE_CLIENT_SECRET',
    'redirect_uri' => '',
    'token_path' => 'storage/youtube/oauth-token.json',
    'default_privacy_status' => 'unlisted',
],
```

If `redirect_uri` is left empty, the module uses:

```text
{base_url}/admin/youtube/callback.php
```

## Connect The Channel

1. Log into the module admin.
2. Open `YouTube` from the sidebar.
3. Click `Connecter YouTube`.
4. Authorize the correct YouTube channel.

The OAuth token is stored in `storage/youtube/oauth-token.json`, protected by the existing storage `.htaccess`.

## Create A Live From A Diffusion

1. Create or edit a diffusion.
2. Save the diffusion first.
3. Open the saved diffusion edit page.
4. Use `Créer le live YouTube`.

The module sends the diffusion title, description, date, duration, and privacy setting to YouTube. When YouTube returns the live video ID, the module fills the YouTube Live field automatically.

## Notes

- Default YouTube visibility is controlled by `youtube.default_privacy_status`.
- Accepted values are `private`, `unlisted`, and `public`.
- The created live still needs to be managed on YouTube for actual streaming.
- If the channel is not enabled for live streaming, YouTube will reject the API request.
- If Google revokes the refresh token, reconnect the channel from the YouTube admin page.
