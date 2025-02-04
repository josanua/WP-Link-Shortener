# WP-Link-Shortener
A WordPress plugin enabling authorized users to create, manage, and track short links.

## Plugin Features

### Core Features
#### 1. Link Management
- Add a settings page in the admin panel where users can create short links for any URL.
- Allow users to manually specify custom short link addresses.
- Display a table listing all short links with their corresponding click counts below the form.

#### 2. Short Link Redirection & Tracking
- Redirect users to the original URL upon clicking a short link.
- Track the total number of clicks for each short link.

### Optional Features
#### 3. Link Table Management
- Enable link deletion directly from the table.
- Provide search functionality for finding links in the table.

#### 4. Advanced Statistics
- Add detailed analytics for each link, such as:
    - Date and time of clicks
    - IP address
    - Referrer information

#### 5. Settings & Security
- Allow administrators to override roles and permissions with a hook.


### Plugin develop methods
For creating the skeleton, I decided to use the WP CLI functionality 'wp scaffold plugin'.
Used singleton pattern

