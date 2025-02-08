# WP-Link-Shortener
A WordPress plugin enabling authorized users to create, manage, and track short links.

## Plugin Features

### Core Features
#### 1. Link Management
- Done - Add a settings page in the admin panel where users can create short links for any URL.
- Done - Allow users to manually specify custom short link addresses.
- Done - Display a table listing all short links with their corresponding click counts below the form.

#### 2. Short Link Redirection & Tracking
- Done - Redirect users to the original URL upon clicking a short link.
- Done - Track the total number of clicks for each short link.

### Optional Features
#### 3. Link Table Management
- Enable link deletion directly from the table.
- Provide search functionality for finding links in the table.

#### 4. Advanced Statistics
- Add detailed analytics for each link, such as:
    - Done - Date and time of clicks
    - Done - IP address
    - Done - Referrer information

#### 5. Settings & Security
- Allow administrators to override roles and permissions with a hook.
- Done - Ensure data security and access rights verification.

#### 6. UX/UI Features
- Done - Shows successful or error message

### Plugin develop methods
#### Coding
For creating the skeleton, I decided to use the WP CLI functionality 'wp scaffold plugin'.
Used singleton pattern

#### Architecture
I choose do create with Custom Database Table instead of CPT because this architecture is efficient for larger datasets and provides full control over how the data is stored, queried, and managed.
It was possible to ressolve with Custom Post Type, in this case wasn't necessary to work with UI WP Classes, seems like that in provided design.

#### TODO
Delete table and do cleaning stuff on plugin deletion

#### Issues
Was choosen to do stats funcs on back end with php code, will be necessary to include with JS to have much more control on client side.
