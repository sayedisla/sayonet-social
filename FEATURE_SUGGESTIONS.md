# Circle Mini - Feature Suggestions for Web Practice Lab

A mini social platform project (like Circle/Twitter) for beginner web development students to practice PHP, MySQL, HTML, CSS, and JavaScript.

## Core Social Features

### 1. ✨ Profile Update Page (BEGINNER - Detailed Instructions Below)
Add a profile page where users can update their name, bio, and see their registration date.

### 2. 📝 Create Posts/Status Updates
Allow users to write and publish short text posts (like tweets/status updates) visible on their dashboard.

### 3. 📰 User Feed/Timeline
Display all posts from all users in chronological order on the main feed page.

### 4. 💬 Comment on Posts
Let users comment on posts with nested replies displayed below each post.

### 5. ❤️ Like/Unlike Posts
Add a like button to posts with a counter showing total likes and ability to unlike.

### 6. 👥 User Profile Pages
Create public profile pages showing user info, their posts, follower count, and join date.

### 7. 🔄 Follow/Unfollow Users
Implement following system where users can follow/unfollow others and see a personalized feed.

### 8. 🔔 Basic Notifications
Notify users when someone likes their post, comments, or follows them.

### 9. 🔍 Search Users and Posts
Add search functionality to find users by name or search posts by keyword.

### 10. 📷 Image Upload for Posts
Allow users to attach images to their posts with preview before posting.

## Profile & Authentication Features

### 11. 👤 User Avatar Upload
Allow users to upload a profile picture that displays on their posts and profile.

### 12. 📝 User Bio/About Section
Add a bio field where users can write about themselves (max 160 characters).

### 13. 🔐 Password Reset via Email
Implement "Forgot Password?" functionality that sends a reset link to the user's email.

### 14. ✅ Email Verification
Require users to verify their email address before they can post or interact.

### 15. 📊 User Statistics Widget
Display user stats: total posts, followers, following, likes received, and member since date.

## Engagement Features

### 16. 🔖 Bookmark/Save Posts
Let users save posts to a private bookmarks collection for later viewing.

### 17. 🔁 Repost/Share Posts
Allow users to repost others' content to their own timeline with optional comment.

### 18. #️⃣ Hashtags
Add clickable hashtags to posts that show all posts with the same hashtag.

### 19. 📌 Pin Post to Profile
Let users pin their favorite post to the top of their profile page.

### 20. 🏆 User Mentions (@username)
Allow mentioning other users in posts with @username that links to their profile.

## Content Management

### 21. ✏️ Edit Posts
Allow users to edit their own posts within 24 hours of posting with "edited" indicator.

### 22. 🗑️ Delete Posts
Let users delete their own posts with a confirmation dialog.

### 23. 🚫 Report Posts/Users
Add reporting functionality for inappropriate content with reason selection.

### 24. 📊 Post Analytics
Show post owners how many views, likes, and comments their posts received.

### 25. 💾 Draft Posts
Allow users to save post drafts before publishing them.

## UI/UX Improvements

### 26. 📱 Responsive Design
Make the platform fully responsive for mobile, tablet, and desktop devices.

### 27. 🎨 Dark Mode Toggle
Add a theme switcher that lets users toggle between light and dark mode.

### 28. 🚫 Error Flash Messages
Create a flash message system for success/error notifications with auto-dismiss.

### 29. ⏬ Infinite Scroll for Feed
Load more posts automatically as users scroll down instead of pagination.

### 30. 🔒 Password Strength Indicator
Create a visual password strength meter during registration.

## Advanced Features

### 31. 💬 Direct Messages (DMs)
Implement private messaging between users with conversation history.

### 32. 🔔 Real-time Notifications
Show live notification count updates without page refresh (using AJAX).

### 33. 📈 Trending Topics
Display trending hashtags and popular posts on the sidebar.

### 34. 🎯 Interest-Based Recommendations
Suggest users to follow based on similar interests or popular users.

### 35. 🔄 Activity Feed
Show a feed of recent activities (new followers, likes, comments) on dashboard.

### 36. 🌐 Public vs Private Profiles
Let users make their profile private so only followers can see their posts.

### 37. 🎭 User Roles (Admin/Moderator)
Create admin panel with ability to manage users, delete posts, and ban accounts.

### 38. 📅 Post Scheduling
Allow users to schedule posts to be published at a future date/time.

### 39. 📊 Admin Dashboard
Create admin statistics showing total users, posts, daily active users, and growth charts.

### 40. 🔐 Two-Factor Authentication
Add 2FA security using email or authenticator app codes.

---

## 🎓 Feature #1: Profile Update Page - Detailed Lab Instructions

### Learning Objectives
By completing this feature, you will learn:
- Creating new routes and controller methods
- Working with forms and POST data
- Database UPDATE operations with PDO
- Passing data from controllers to views
- Basic form validation
- Formatting dates in PHP

### Prerequisites
- Basic understanding of PHP syntax
- Familiarity with HTML forms
- Understanding of how the existing AuthBoard routing works

---

### Step 1: Update the Database Schema (5 minutes)

**What you'll do:** The current `users` table already has a `created_at` timestamp, but let's verify it exists.

1. Open your database management tool (phpMyAdmin, TablePlus, or MySQL Workbench)
2. Connect to your `authboard` database
3. Check the `users` table structure - you should see a `created_at` column
4. If it doesn't exist, run this SQL:
   ```sql
   ALTER TABLE users 
   ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
   ```

**Why?** We need to display when the user registered on the profile page.

---

### Step 2: Add a Profile Route (10 minutes)

**What you'll do:** Create a new route for the profile page.

1. Open `index.php` in the root directory
2. Find the section where routes are defined (around line 33-40)
3. Add these two new routes after the dashboard route:

```php
$router->get('/profile', fn() => $dash->profile());
$router->post('/profile', fn() => $dash->updateProfile());
```

**Explanation:**
- The first route displays the profile form (GET request)
- The second route handles form submission (POST request)
- Both routes use the `DashboardController` since this is for logged-in users

**Test it:** Try visiting `http://localhost:8000/profile` - you'll get an error because we haven't created the method yet. That's expected!

---

### Step 3: Create Controller Methods (15 minutes)

**What you'll do:** Add the profile methods to `DashboardController`.

1. Open `app/Controllers/DashboardController.php`
2. Add these two methods at the end of the class (before the closing brace):

```php
public function profile() {
    $user = Session::get('user');
    if (!$user) {
        header('Location: /login');
        exit;
    }
    
    // Get full user data including created_at
    $stmt = User::connect()->prepare('SELECT id, name, email, created_at FROM users WHERE id = ?');
    $stmt->execute([$user['id']]);
    $fullUser = $stmt->fetch();
    
    $this->view('profile.php', ['user' => $fullUser]);
}

public function updateProfile() {
    $user = Session::get('user');
    if (!$user) {
        header('Location: /login');
        exit;
    }
    
    $name = trim($_POST['name'] ?? '');
    
    // Validation
    if (strlen($name) < 2) {
        Session::set('error', 'Name must be at least 2 characters.');
        header('Location: /profile');
        exit;
    }
    
    // Update database
    $stmt = User::connect()->prepare('UPDATE users SET name = ? WHERE id = ?');
    $stmt->execute([$name, $user['id']]);
    
    // Update session
    $user['name'] = $name;
    Session::set('user', $user);
    Session::set('success', 'Profile updated successfully!');
    
    header('Location: /profile');
}
```

**Problem!** Notice we're calling `User::connect()` but that method is private. We need to fix that.

3. Open `app/Models/User.php`
4. Change line 7 from `private static function connect()` to:
   ```php
   public static function connect(): PDO {
   ```

**Explanation:**
- `profile()` checks if user is logged in and fetches their full data including registration date
- `updateProfile()` validates the new name, updates the database, and updates the session
- We use `Session::set()` for flash messages (you'll implement display later)
- `header('Location: ...')` redirects the user after processing

---

### Step 4: Create the Profile View (20 minutes)

**What you'll do:** Create the HTML form for the profile page.

1. Create a new file: `app/Views/profile.php`
2. Add this code:

```php
<?php
$title = 'Profile | AuthBoard';
ob_start();
?>

<h2>My Profile</h2>

<?php if (Session::get('success')): ?>
    <div class="message success">
        <?= htmlspecialchars(Session::get('success')) ?>
        <?php Session::remove('success'); ?>
    </div>
<?php endif; ?>

<?php if (Session::get('error')): ?>
    <div class="message error">
        <?= htmlspecialchars(Session::get('error')) ?>
        <?php Session::remove('error'); ?>
    </div>
<?php endif; ?>

<form method="POST" action="/profile" class="form">
    <label for="name">Name</label>
    <input type="text" 
           id="name" 
           name="name" 
           value="<?= htmlspecialchars($user['name']) ?>" 
           required 
           minlength="2" />
    
    <label>Email (cannot be changed)</label>
    <input type="email" 
           value="<?= htmlspecialchars($user['email']) ?>" 
           disabled 
           style="background: #f0f0f0; cursor: not-allowed;" />
    
    <button type="submit">Update Profile</button>
</form>

<div style="margin-top: 24px; padding: 16px; background: #f9fafb; border-radius: 6px;">
    <h3 style="margin-top: 0; font-size: 16px;">Account Information</h3>
    <p style="font-size: 14px; color: #666;">
        <strong>Member since:</strong> 
        <?= date('F j, Y', strtotime($user['created_at'])) ?>
        <br>
        <small>(<?= date('g:i A', strtotime($user['created_at'])) ?>)</small>
    </p>
</div>

<p style="margin-top: 16px;">
    <a href="/dashboard">← Back to Dashboard</a>
</p>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
```

**Explanation:**
- We use `ob_start()` and `ob_get_clean()` to capture output for the layout
- Flash messages (success/error) are displayed and then removed from session
- `htmlspecialchars()` prevents XSS attacks by escaping HTML
- The email field is disabled because we don't want users changing it
- `date()` formats the timestamp nicely (e.g., "January 15, 2025")

---

### Step 5: Add Session Helper Methods (10 minutes)

**What you'll do:** Add methods to manage flash messages in the Session class.

1. Open `app/Core/Session.php`
2. Add this method at the end of the class:

```php
public static function remove(string $key): void {
    unset($_SESSION[$key]);
}
```

**Why?** This lets us delete session variables like flash messages after displaying them.

---

### Step 6: Style the Messages (10 minutes)

**What you'll do:** Add CSS for success and error messages.

1. Open `assets/style.css`
2. Add these styles at the end:

```css
.message {
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 16px;
    font-size: 14px;
}
.message.success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #6ee7b7;
}
.message.error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}
```

**Explanation:**
- `.message` is the base style for notification boxes
- `.success` uses green colors for positive feedback
- `.error` uses red colors for validation errors

---

### Step 7: Add Link to Profile (5 minutes)

**What you'll do:** Add a profile link in the navigation.

1. Open `app/Views/layout.php`
2. Find the navigation section (around line 13)
3. Replace the nav line with:

```php
<nav><a href="/dashboard">Dashboard</a> | <a href="/profile">Profile</a> | <a href="/logout">Logout</a></nav>
```

**Why?** Users need a way to access the profile page from the main navigation.

---

### Step 8: Test Your Feature (10 minutes)

**Testing checklist:**

1. **Navigate to profile:**
   - Visit `http://localhost:8000/profile`
   - You should see your current name and registration date

2. **Update your name:**
   - Change your name to something new
   - Click "Update Profile"
   - You should see a success message
   - Your name should be updated everywhere (dashboard, navigation)

3. **Test validation:**
   - Try to submit a name with only 1 character
   - You should see an error message
   - The form should not submit

4. **Check the database:**
   - Open your database tool
   - Look at the `users` table
   - Verify your name was actually updated

5. **Test edge cases:**
   - Try entering HTML: `<script>alert('xss')</script>` as name
   - It should be displayed as text, not executed
   - Try very long names (100+ characters)

---

### Common Errors and Solutions

**Error 1: "Call to undefined method User::connect()"**
- **Solution:** Make sure you changed the `connect()` method from `private` to `public` in `app/Models/User.php`

**Error 2: "Call to undefined method Session::remove()"**
- **Solution:** Add the `remove()` method to `app/Core/Session.php` as shown in Step 5

**Error 3: Flash messages not showing**
- **Solution:** Make sure you're using `Session::set('success', 'message')` in the controller and that you call `Session::remove()` after displaying in the view

**Error 4: Page not found**
- **Solution:** Verify you added both routes in `index.php` and that they point to the correct controller methods

**Error 5: Styles not applying**
- **Solution:** Clear your browser cache (Cmd+Shift+R on Mac, Ctrl+F5 on Windows) and verify the CSS path is correct

---

### Challenge Extensions (Optional)

Once you've completed the basic feature, try these enhancements:

1. **Add password change functionality**
   - Add fields for current password, new password, and confirm password
   - Verify the current password before allowing change
   - Require new password to be at least 8 characters

2. **Add client-side validation**
   - Use JavaScript to validate the form before submission
   - Show inline error messages without refreshing the page

3. **Add a profile photo placeholder**
   - Display user's initials in a colored circle as a temporary avatar
   - Use CSS to make it look nice

4. **Track last profile update**
   - Add a `updated_at` column to the users table
   - Display "Last updated: X days ago" on the profile page

---

### What You Learned

By completing this feature, you've practiced:

✅ **Routing** - Adding GET and POST routes  
✅ **Controllers** - Creating methods to handle requests  
✅ **Models** - Writing SQL UPDATE queries with PDO  
✅ **Views** - Building forms and displaying data  
✅ **Sessions** - Managing user data and flash messages  
✅ **Validation** - Checking user input before saving  
✅ **Security** - Using `htmlspecialchars()` to prevent XSS  
✅ **CSS** - Styling messages and forms  
✅ **Testing** - Verifying your code works correctly  

### Next Steps

After completing this feature, you can move on to Feature #2 (Email Validation) or choose any other feature from the list above. Each feature builds on the concepts you've learned here!

---

**Need Help?**
- Review the existing code in `AuthController.php` for similar patterns
- Check the PHP documentation: https://www.php.net/manual/en/
- Ask your instructor or teaching assistant
- Use `var_dump($variable)` to debug and see what data you're working with
