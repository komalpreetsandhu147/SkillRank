# SKILLRANK – AI-Powered Skill Assessment & Career Readiness Platform

A full-stack college Tech Fest project built with **HTML, CSS, JavaScript, PHP, MySQL**, and optional **Google Gemini AI**.

SkillRank provides university students with an end-to-end continuous learning and verification loop:
**ASSESS → ANALYSE → IDENTIFY SKILL GAPS → PRACTICE → IMPROVE → RANK → SHOWCASE**

---

## 🌟 Key Features

### 👨‍🎓 Student Module
- **Registration & Authentication**: Secure sign-up with password hashing (`password_hash`), session protection, and 1-click demo logins for Tech Fest presentation.
- **Student Profile**: Customize academic details, university, course, semester, target career role, and bio.
- **Skill-Based Assessments**: Timed interactive multiple-choice tests in **PHP, JavaScript, DBMS, and HTML & CSS**.
- **Instant Automatic Evaluation**: Calculates score, total marks, accuracy percentage, and time taken.
- **Skill Progression & Levels**:
  - 👑 **Expert (90% - 100%)**
  - ⭐ **Advanced (75% - 89%)**
  - 🔷 **Intermediate (55% - 74%)**
  - 🌱 **Beginner (Below 55%)**
- **Detailed Question Review**: Shows student's answer vs. correct answer with detailed explanations.
- **AI Performance Diagnostics & Skill Gaps**: Highlights top strengths and identifies critical skill gaps with an actionable study plan.
- **Campus Leaderboard**: Real-time rankings with Top-3 Gold/Silver/Bronze podium and user row highlighting.
- **Verified Skill-Based Resume**: Auto-generates a print-perfect, 1-page A4 resume with AI summary, verified skills, and PDF export (`window.print()`).

### 👩‍💼 Admin & Faculty Module
- **Separate Admin Console**: Secure admin sign-in with access control.
- **Operations Dashboard**: KPIs (Total Students, Question Bank size, Tests Taken, Network Accuracy).
- **Curriculum & Subject Management**: Add/manage subject tracks (code, icon, description) and syllabus topics.
- **Question Bank CRUD**:
  - Filter questions by Subject and Difficulty (Easy, Medium, Hard).
  - Add, Edit, and Delete questions with option distractors, marks, and explanations.
  - **✨ GenAI Question Generator**: Generate and import balanced multiple-choice questions automatically using Gemini AI (with smart procedural fallback).
- **Student Directory & Reports**: Searchable student directory with detailed printable transcripts and diagnostic reports.
- **Institutional Analytics**: Pass rates, cohort-wide skill gaps, difficulty distribution, and intervention suggestions.

### 🤖 GenAI Integration (Gemini 1.5 API)
- **Question Generation**: Auto-generates questions for any subject and difficulty.
- **Skill Gap Detection**: Synthesizes student attempts and highlights exact concepts to review.
- **Resume Summary Generator**: Writes ATS-optimized professional career summaries from verified student skill scores.
- **Graceful Fallback**: If `GEMINI_API_KEY` is not configured or offline, the platform runs seamlessly on its built-in rule-based algorithmic engine.

---

## 📁 Project Structure

```text
SkillRank/
├── index.php                # Landing page with Tech Fest highlights & quick demo access
├── about.php                # How It Works & 7-step learning loop
├── login.php                # Student sign-in with 1-click demo buttons
├── register.php             # Student registration with skill & resume initialization
├── logout.php               # Secure session termination
├── dashboard.php            # Student dashboard with KPIs, skills snapshot & activity
├── profile.php              # Student profile, target role & career settings
├── practice.php             # Assessment track selection & targeted skill gap practice
├── quiz.php                 # Timed skill test engine with countdown & progress tracker
├── result.php               # Instant score evaluation, skill level update & explanations
├── analytics.php            # AI diagnostics, accuracy trajectory chart & skill gaps
├── leaderboard.php          # Campus leaderboard with top-3 podium
├── resume.php               # Verified skill-based resume with AI summary & PDF export
├── admin_login.php          # Dedicated faculty/admin authentication
├── admin_dashboard.php      # Admin overview with KPIs and live assessment stream
├── questions.php            # Question bank management with filtering & AI generator
├── question_form.php        # Add / Edit question with dynamic topics & explanations
├── subjects.php             # Subject tracks & curriculum topics management
├── students.php             # Searchable student directory
├── student_profile.php      # Printable student assessment & diagnostic report
├── admin_analytics.php      # Institutional pass rates & curriculum intervention gaps
├── ai.php                   # Gemini 1.5 REST API engine with procedural fallback
├── auth.php                 # Session authentication & role guards
├── config.php               # Database & API configuration
├── db.php                   # Database connection & prepared statement helpers
├── database.sql             # Complete schema, views, and seed data
├── assets/
│   ├── style.css            # Responsive CSS design system (DM Sans & Space Grotesk)
│   └── app.js               # Canvas chart visualizer & animated progress bars
└── includes/
    ├── header.php           # Responsive sidebar & navigation shell
    └── footer.php           # Script loader & closing HTML shell
```

---

## 🚀 Running on XAMPP (Localhost)

1. **Start XAMPP**:
   - Open the **XAMPP Control Panel**.
   - Start **Apache** and **MySQL**.
2. **Import Database**:
   - Open your browser to `http://localhost/phpmyadmin/`.
   - Create a database named `skillrank` (or let `database.sql` create it).
   - Click **Import** and select `database.sql` from this folder.
3. **Configure Project Location**:
   - Copy this project folder to `C:\xampp\htdocs\SkillRank` (or `C:\xampp\htdocs\techfest`).
   - Open `http://localhost/SkillRank/` or `http://localhost/techfest/`.
4. **Database Configuration**:
   - If your MySQL root account has a password, update `DB_PASS` in `config.php`.
5. **(Optional) Configure Gemini API**:
   - In `config.php`, enter your Gemini API key from [Google AI Studio](https://aistudio.google.com/):
     ```php
     defined('GEMINI_API_KEY') or define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY');
     ```
   - If left blank, SkillRank automatically operates in its smart procedural engine mode.

---

## 🔑 Demo Accounts (For Presentation)

| Role | Email | Password | Quick Link |
| :--- | :--- | :--- | :--- |
| **Top Student** | `aarav@skillrank.demo` | `demo123` | [1-Click Sign In](http://localhost/SkillRank/login.php?demo=aarav) |
| **Active Student** | `meera@skillrank.demo` | `demo123` | [1-Click Sign In](http://localhost/SkillRank/login.php?demo=meera) |
| **Admin / Faculty** | `admin@skillrank.demo` | `demo123` | [1-Click Sign In](http://localhost/SkillRank/admin_login.php?demo=admin) |

---

## 🧪 Testing the Complete Workflow

### 1. Student Flow
1. Go to `http://localhost/SkillRank/login.php` → Click **Student Aarav**.
2. Visit **Dashboard** (`dashboard.php`) to see verified skills, score, and rank.
3. Visit **Practice Tests** (`practice.php`) → Click **Assess Now** on **PHP** or **JavaScript**.
4. In the **Skill Test** (`quiz.php`), answer questions with the live timer and progress bar.
5. Click **Submit Assessment** → View **Result Page** (`result.php`) with score, accuracy %, updated skill level, and detailed answer explanations.
6. Visit **Analytics & AI** (`analytics.php`) to inspect the accuracy chart and AI Skill Gap Diagnostic report.
7. Check **Leaderboard** (`leaderboard.php`) to view the podium and student rank.
8. Visit **Skill Resume** (`resume.php`) → Click **Generate AI Summary** → Click **Print / Save PDF**!

### 2. Admin Flow
1. Go to `http://localhost/SkillRank/admin_login.php` → Click **Login as Admin**.
2. In **Admin Overview** (`admin_dashboard.php`), view network KPIs and live test feed.
3. Open **Question Bank** (`questions.php`) → Click **AI Question Generator** → Choose **JavaScript (Medium)** → Click **Generate & Import**!
4. Click **+ Add New Question** (`question_form.php`) to add a custom question with distractors and explanation.
5. Export or import JSON question banks in bulk (`question_export.php`).
6. Visit **Students Directory** (`students.php`) → Search for "Aarav" → Click **Student Report** (`student_profile.php`) to review his diagnostic transcript and print the report.
7. Check **AI Diagnostics** (`admin_ai.php`) to monitor real-time Gemini API ping latency.
8. Check **System Analytics** (`admin_analytics.php`) to review institutional pass rates and widespread curriculum gaps.

---

## 🚀 Advanced Tech Fest Features

1. **Interactive AI Mentor Widget (`ai_chat.php`)**:
   - Floating assistant drawer accessible across student workspaces.
   - Provides instant concept explanations, code debugging advice, and technical interview guidance via Gemini 1.5 Flash (with smart offline CS knowledge base fallback).
2. **Flashcard Revision Studio (`flashcards.php`)**:
   - Active recall and spaced repetition deck with 3D card flips.
   - Self-mastery tracking, keyboard navigation (`Space` to flip, `← / →` to navigate), and curriculum track filters.
3. **Interactive Code Sandbox (`playground.php`)**:
   - Hands-on frontend development lab with live sandboxed iframe preview.
   - Starter templates for DOM Counter, CSS Glassmorphism, SQL Query Mocking, and JavaScript array algorithms.
   - Intercepts iframe console logs into an in-browser virtual terminal.
4. **Official Printable Certificate of Competence (`certificate.php`)**:
   - High-resolution, ornate academic certificate with gold/emerald borders, verified score, and faculty signoff.
   - Dynamic QR code linking directly to public verification (`verify_credential.php?id=...`).
   - Clean `@media print` CSS optimized for 1-click A4 PDF export.
5. **Public Credential Verification (`verify_credential.php`)**:
   - Open verification URL suitable for LinkedIn, GitHub, or recruiter resumes.
   - Displays real-time verified skill radar, test accuracy, and campus ranking.
6. **Dark Mode / Midnight Theme**:
   - Seamless one-click theme switcher in the sidebar, persistent across sessions via `localStorage`.
   - Dynamic Canvas chart re-theming for high-contrast accessibility.

