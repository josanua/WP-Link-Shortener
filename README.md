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

### Plugin Development Methods
#### Coding
- Used the `wp scaffold plugin` functionality from WP CLI to create the initial plugin structure.
- Implemented the **Singleton Pattern** for consistency and to prevent duplication.

#### Architecture
- Decided to use **Custom Database Table** instead of Custom Post Type (CPT) to better handle larger datasets. This approach ensures efficient control over data storage, querying, and management. While a Custom Post Type could have been a viable solution,
- Working with UI WP Classes wasn't required based on the plugin's design.

#### TODO
- Add functionality to delete the database table and clean up resources upon plugin deletion.

#### Issues
- Statistics functionality is currently implemented on the backend using PHP. To enhance usability and flexibility, JavaScript integration is necessary for better control on the client side.

### Technical Requirements
- Minimum PHP version: **7.4**
