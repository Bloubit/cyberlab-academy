# CyberLab Academy - ER Diagram

## Text-Based Entity Relationship Diagram

```
┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                                                                                                                   │
│                                    CYBERLAB ACADEMY DATABASE ARCHITECTURE                                                          │
│                                                                                                                                   │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                            AUTHENTICATION & AUTHORIZATION                                                         │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

    ┌──────────────────┐         ┌──────────────────┐         ┌──────────────────┐
    │      roles       │         │  role_permissions│         │   permissions    │
    ├──────────────────┤    ┌───►├──────────────────┤◄───┐    ├──────────────────┤
    │ id (PK)          │    │    │ role_id (FK)     │    │    │ id (PK)          │
    │ name             │    │    │ permission_id(FK)│    │    │ name             │
    │ slug             │    │    │ created_at       │    │    │ slug             │
    │ description      │    │    └──────────────────┘    │    │ module           │
    │ is_system        │    │                            │    │ description      │
    │ created_at       │    │                            │    │ created_at       │
    │ updated_at       │    │                            │    └──────────────────┘
    └──────────────────┘    │
                            │    ┌──────────────────┐
                            │    │   user_roles     │
                            │    ├──────────────────┤
                            └───►│ user_id (FK)     │
                                 │ role_id (FK)     │
                                 │ assigned_at      │
                                 │ assigned_by (FK) │
                                 └──────────────────┘
                                         │
                                         ▼
    ┌─────────────────────────────────────────────────────────────────────────────────────────────┐
    │                                      users                                                  │
    ├─────────────────────────────────────────────────────────────────────────────────────────────┤
    │ id (PK, UUID)        │ email              │ email_verified_at  │ password                  │
    │ name                 │ username           │ avatar             │ bio                       │
    │ phone                │ country_code       │ timezone           │ language                  │
    │ is_active            │ is_banned          │ ban_reason         │ last_login_at             │
    │ last_login_ip        │ login_attempts     │ locked_until       │ settings (JSONB)          │
    │ metadata (JSONB)     │ created_at         │ updated_at         │ deleted_at                │
    └─────────────────────────────────────────────────────────────────────────────────────────────┘
                                         │
                                         │
        ┌────────────────────────────────┼────────────────────────────────┐
        │                                │                                │
        ▼                                ▼                                ▼
┌──────────────────┐         ┌──────────────────┐         ┌──────────────────┐
│ user_sessions    │         │ user_xp_summary  │         │  notifications   │
├──────────────────┤         ├──────────────────┤         ├──────────────────┤
│ id (PK, UUID)    │         │ user_id (PK,FK)  │         │ id (PK, UUID)    │
│ user_id (FK)     │         │ total_xp         │         │ user_id (FK)     │
│ ip_address       │         │ level            │         │ type             │
│ user_agent       │         │ xp_to_next_level │         │ title            │
│ payload          │         │ current_streak   │         │ message          │
│ last_activity    │         │ longest_streak   │         │ data (JSONB)     │
│ created_at       │         │ last_activity_at │         │ read_at          │
└──────────────────┘         │ updated_at       │         │ action_url       │
                             └──────────────────┘         │ priority         │
                                                          │ channel          │
                                                          │ sent_at          │
                                                          │ created_at       │
                                                          └──────────────────┘


┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                              COURSES & LEARNING                                                                  │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

    ┌──────────────────────┐
    │  course_categories   │
    ├──────────────────────┤
    │ id (PK)              │
    │ parent_id (FK, self) │──────────┐ (Self-referencing for hierarchy)
    │ name                 │          │
    │ slug                 │          │
    │ description          │          │
    │ icon                 │          │
    │ color                │          │
    │ sort_order           │          │
    │ is_active            │          │
    │ metadata (JSONB)     │          │
    │ created_at           │          │
    │ updated_at           │          │
    └──────────────────────┘          │
            │                         │
            │ 1:N                     │
            ▼                         │
    ┌──────────────────────┐          │
    │      courses         │◄─────────┘
    ├──────────────────────┤
    │ id (PK, UUID)        │
    │ category_id (FK)     │
    │ instructor_id (FK)   │──────┐
    │ title                │      │
    │ slug                 │      │
    │ subtitle             │      │
    │ description          │      │
    │ thumbnail            │      │
    │ preview_video_url    │      │
    │ difficulty_level     │      │
    │ estimated_hours      │      │
    │ price                │      │
    │ currency             │      │
    │ is_free              │      │
    │ is_published         │      │
    │ is_featured          │      │
    │ enrollment_limit     │      │
    │ requirements ([])    │      │
    │ learning_outcomes([])│      │
    │ tags ([])            │      │
    │ stats (JSONB)        │      │
    │ settings (JSONB)     │      │
    │ published_at         │      │
    │ created_at           │      │
    │ updated_at           │      │
    │ deleted_at           │      │
    └──────────────────────┘      │
            │                     │
            │ 1:N                 │ 1:N
            ▼                     ▼
    ┌──────────────────────┐  ┌──────────────────────┐
    │    modules           │  │ course_enrollments   │
    ├──────────────────────┤  ├──────────────────────┤
    │ id (PK, UUID)        │  │ id (PK, UUID)        │
    │ course_id (FK)       │  │ user_id (FK)         │──────┐
    │ title                │  │ course_id (FK)       │      │
    │ description          │  │ status               │      │
    │ sort_order           │  │ progress             │      │
    │ is_published         │  │ enrolled_at          │      │
    │ settings (JSONB)     │  │ completed_at         │      │
    │ created_at           │  │ last_accessed_at     │      │
    │ updated_at           │  │ certificate_id       │      │
    └──────────────────────┘  └──────────────────────┘      │
            │                                               │
            │ 1:N                                           │
            ▼                                               │
    ┌──────────────────────┐                                │
    │      lessons         │                                │
    ├──────────────────────┤                                │
    │ id (PK, UUID)        │                                │
    │ module_id (FK)       │                                │
    │ title                │                                │
    │ slug                 │                                │
    │ content_type         │                                │
    │ content (TEXT)       │                                │
    │ sort_order           │                                │
    │ is_published         │                                │
    │ is_free              │                                │
    │ duration_minutes     │                                │
    │ xp_reward            │                                │
    │ settings (JSONB)     │                                │
    │ created_at           │                                │
    │ updated_at           │                                │
    └──────────────────────┘                                │
            │                                               │
            ├─────────────────┬─────────────────┐           │
            │ 1:N             │ 1:N             │ 1:N       │
            ▼                 ▼                 ▼           │
    ┌──────────────┐  ┌──────────────┐  ┌──────────────┐   │
    │lesson_resources│ │    videos    │ │lesson_progress│   │
    ├──────────────┤  ├──────────────┤  ├──────────────┤   │
    │ id (PK, UUID)│  │ id (PK, UUID)│  │ id (PK, UUID)│   │
    │ lesson_id(FK)│  │ lesson_id(FK)│  │ user_id (FK) │◄──┘
    │ title        │  │ title        │  │ lesson_id(FK)│
    │ file_url     │  │ url          │  │ status       │
    │ file_type    │  │ thumbnail_url│  │ progress     │
    │ file_size    │  │ duration_secs│  │ time_spent   │
    │ sort_order   │  │ hls_url      │  │ last_position│
    │ download_cnt │  │ subtitles    │  │ completed_at │
    │ created_at   │  │ metadata     │  │ created_at   │
    └──────────────┘  │ created_at   │  │ updated_at   │
                      └──────────────┘  └──────────────┘


┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                              QUIZZES & EXAMS                                                                     │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

    ┌──────────────────────┐
    │      quizzes         │
    ├──────────────────────┤
    │ id (PK, UUID)        │
    │ lesson_id (FK, opt)  │
    │ course_id (FK)       │
    │ title                │
    │ description          │
    │ quiz_type            │
    │ time_limit_minutes   │
    │ passing_score        │
    │ max_attempts         │
    │ shuffle_questions    │
    │ shuffle_answers      │
    │ show_correct_answers │
    │ is_published         │
    │ settings (JSONB)     │
    │ created_at           │
    │ updated_at           │
    └──────────────────────┘
            │
            │ 1:N
            ▼
    ┌──────────────────────┐
    │     questions        │
    ├──────────────────────┤
    │ id (PK, UUID)        │
    │ quiz_id (FK)         │
    │ question_type        │
    │ question_text        │
    │ question_html        │
    │ code_snippet         │
    │ code_language        │
    │ explanation          │
    │ points               │
    │ sort_order           │
    │ difficulty           │
    │ metadata (JSONB)     │
    │ created_at           │
    │ updated_at           │
    └──────────────────────┘
            │
            │ 1:N
            ▼
    ┌──────────────────────┐
    │      answers         │
    ├──────────────────────┤
    │ id (PK, UUID)        │
    │ question_id (FK)     │
    │ answer_text          │
    │ is_correct           │
    │ sort_order           │
    │ match_pair_id        │
    │ explanation          │
    │ created_at           │
    └──────────────────────┘


    ┌──────────────────────┐         ┌──────────────────────┐
    │    quiz_attempts     │         │    exam_results      │
    ├──────────────────────┤         ├──────────────────────┤
    │ id (PK, UUID)        │         │ id (PK, UUID)        │
    │ user_id (FK)         │         │ user_id (FK)         │
    │ quiz_id (FK)         │         │ quiz_id (FK)         │
    │ score                │         │ score                │
    │ total_points         │         │ total_questions      │
    │ is_passed            │         │ correct_answers      │
    │ time_taken_seconds   │         │ time_taken_seconds   │
    │ started_at           │         │ is_passed            │
    │ completed_at         │         │ certificate_earned   │
    │ answers (JSONB)      │         │ attempt_number       │
    │ ip_address           │         │ started_at           │
    │ user_agent           │         │ completed_at         │
    └──────────────────────┘         │ reviewed_at          │
                                     │ reviewed_by (FK)     │
                                     │ feedback             │
                                     │ metadata (JSONB)     │
                                     └──────────────────────┘


┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                              CYBER LABS                                                                          │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

    ┌──────────────────────┐
    │        labs          │
    ├──────────────────────┤
    │ id (PK, UUID)        │
    │ category_id (FK)     │
    │ creator_id (FK)      │──────┐
    │ title                │      │
    │ slug                 │      │
    │ description          │      │
    │ difficulty           │      │
    │ lab_type             │      │
    │ estimated_time_mins  │      │
    │ points               │      │
    │ is_published         │      │
    │ is_featured          │      │
    │ requires_subscription│      │
    │ os_type              │      │
    │ tools_required ([])  │      │
    │ hints ([])           │      │
    │ learning_objectives  │      │
    │ stats (JSONB)        │      │
    │ settings (JSONB)     │      │
    │ created_at           │      │
    │ updated_at           │      │
    │ deleted_at           │      │
    └──────────────────────┘      │
            │                     │
            ├─────────────────────┼─────────────────────┐
            │ 1:N                 │ 1:N                 │ 1:N
            ▼                     ▼                     ▼
    ┌──────────────┐      ┌──────────────┐      ┌──────────────┐
    │   machines   │      │ lab_instances│      │  challenges  │
    ├──────────────┤      ├──────────────┤      ├──────────────┤
    │ id (PK, UUID)│      │ id (PK, UUID)│      │ id (PK, UUID)│
    │ lab_id (FK)  │      │ lab_id (FK)  │      │ lab_id (FK)  │
    │ name         │      │ user_id (FK) │◄────┐│ title        │
    │ hostname     │      │ machine_id(FK│◄─┐  ││ description  │
    │ ip_address   │      │ status       │  │  ││ difficulty   │
    │ internal_ip  │      │ expires_at   │  │  ││ points       │
    │ os_type      │      │ started_at   │  │  ││ sort_order   │
    │ os_version   │      │ stopped_at   │  │  ││ challenge_type│
    │ cpu_cores    │      │ reset_count  │  │  ││ is_active    │
    │ ram_mb       │      │ last_reset_at│  │  ││ hints ([])   │
    │ disk_gb      │      │ connection_  │  │  ││ solution     │
    │ status       │      │   info(JSONB)│  │  ││ files (JSONB)│
    │ vm_type      │      │ usage_stats  │  │  ││ metadata     │
    │ provider     │      │   (JSONB)    │  │  ││ created_at   │
    │ provider_id  │      │ created_at   │  │  ││ updated_at   │
    │ credentials  │      │ updated_at   │  │  │└──────────────┘
    │   (JSONB)    │      └──────────────┘  │          │
    │ network_     │             ▲           │          │ 1:N
    │   config     │             │           │          ▼
    │ health_check │             └───────────┘  ┌──────────────┐
    │   _url       │                  1:1       │    flags     │
    │ metadata     │                            ├──────────────┤
    │ created_at   │                            │ id (PK, UUID)│
    │ updated_at   │                            │ challenge_id │
    └──────────────┘                            │   (FK)       │
                                                │ flag_format  │
                                                │ flag_hash    │
                                                │ is_case_     │
                                                │   sensitive  │
                                                │ points       │
                                                │ sort_order   │
                                                │ created_at   │
                                                └──────────────┘
                                                        │
                                                        │ 1:N
                                                        ▼
                                                ┌──────────────┐
                                                │ submissions  │
                                                ├──────────────┤
                                                │ id (PK, UUID)│
                                                │ user_id (FK) │
                                                │challenge_id  │
                                                │   (FK)       │
                                                │ flag_id (FK) │
                                                │ submitted_   │
                                                │   flag       │
                                                │ is_correct   │
                                                │ ip_address   │
                                                │ user_agent   │
                                                │ submitted_at │
                                                │ reviewed_at  │
                                                │ reviewed_by  │
                                                │   (FK)       │
                                                │ feedback     │
                                                └──────────────┘


┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                            GAMIFICATION                                                                          │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

    ┌──────────────────────┐         ┌──────────────────────┐
    │     xp_history       │         │     levels           │
    ├──────────────────────┤         ├──────────────────────┤
    │ id (PK, UUID)        │         │ level (PK)           │
    │ user_id (FK)         │         │ title                │
    │ amount               │         │ min_xp               │
    │ source_type          │         │ max_xp               │
    │ source_id            │         │ badge_icon           │
    │ description          │         │ rewards (JSONB)      │
    │ multiplier           │         └──────────────────────┘
    │ created_at           │
    └──────────────────────┘


    ┌──────────────────────┐         ┌──────────────────────┐
    │      rankings        │         │       badges         │
    ├──────────────────────┤         ├──────────────────────┤
    │ id (PK, UUID)        │         │ id (PK)              │
    │ ranking_type         │         │ name                 │
    │ period               │         │ slug                 │
    │ user_id (FK)         │         │ description          │
    │ score                │         │ icon                 │
    │ rank                 │         │ category             │
    │ metadata (JSONB)     │         │ rarity               │
    │ calculated_at        │         │ xp_reward            │
    └──────────────────────┘         │ requirements (JSONB) │
                                     │ is_active            │
                                     │ is_hidden            │
                                     │ sort_order           │
                                     │ created_at           │
                                     │ updated_at           │
                                     └──────────────────────┘
                                              │
                                              │ 1:N
                                              ▼
                                     ┌──────────────────────┐
                                     │    user_badges       │
                                     ├──────────────────────┤
                                     │ id (PK, UUID)        │
                                     │ user_id (FK)         │
                                     │ badge_id (FK)        │
                                     │ earned_at            │
                                     │ source_type          │
                                     │ source_id            │
                                     │ notified             │
                                     └──────────────────────┘


┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                          CERTIFICATES & VERIFICATION                                                             │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

    ┌──────────────────────┐
    │    certificates      │
    ├──────────────────────┤
    │ id (PK, UUID)        │
    │ user_id (FK)         │
    │ course_id (FK, opt)  │
    │ lab_id (FK, opt)     │
    │ certificate_number   │
    │ certificate_type     │
    │ title                │
    │ description          │
    │ issued_at            │
    │ expires_at           │
    │ pdf_url              │
    │ qr_code              │
    │ verification_url     │
    │ metadata (JSONB)     │
    │ revoked              │
    │ revoked_at           │
    │ revoked_reason       │
    │ created_at           │
    │ updated_at           │
    └──────────────────────┘
            │
            │ 1:N
            ▼
    ┌──────────────────────┐
    │certificate_verifi-   │
    │      cations         │
    ├──────────────────────┤
    │ id (PK, UUID)        │
    │ certificate_id (FK)  │
    │ verified_by (FK)     │
    │ verification_type    │
    │ ip_address           │
    │ user_agent           │
    │ verified_at          │
    │ result               │
    └──────────────────────┘


┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                            PAYMENTS & SUBSCRIPTIONS                                                              │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

    ┌──────────────────────┐
    │       plans          │
    ├──────────────────────┤
    │ id (PK)              │
    │ name                 │
    │ slug                 │
    │ description          │
    │ price                │
    │ currency             │
    │ billing_period       │
    │ trial_days           │
    │ features (JSONB)     │
    │ limits (JSONB)       │
    │ is_active            │
    │ is_popular           │
    │ sort_order           │
    │ stripe_price_id      │
    │ created_at           │
    │ updated_at           │
    └──────────────────────┘
            │
            │ 1:N
            ▼
    ┌──────────────────────┐
    │   subscriptions      │
    ├──────────────────────┤
    │ id (PK, UUID)        │
    │ user_id (FK)         │
    │ plan_id (FK)         │
    │ status               │
    │ stripe_subscription_id│
    │ stripe_customer_id   │
    │ trial_start          │
    │ trial_end            │
    │ current_period_start │
    │ current_period_end   │
    │ cancelled_at         │
    │ ends_at              │
    │ payment_method       │
    │   (JSONB)            │
    │ metadata (JSONB)     │
    │ created_at           │
    │ updated_at           │
    └──────────────────────┘
            │
            │ 1:N
            ▼
    ┌──────────────────────┐
    │      payments        │
    ├──────────────────────┤
    │ id (PK, UUID)        │
    │ user_id (FK)         │
    │ subscription_id (FK) │
    │ amount               │
    │ currency             │
    │ status               │
    │ payment_method       │
    │ stripe_payment_id    │
    │ stripe_invoice_id    │
    │ description          │
    │ metadata (JSONB)     │
    │ refunded_at          │
    │ refund_reason        │
    │ created_at           │
    │ updated_at           │
    └──────────────────────┘


┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                            ANALYTICS & TRACKING                                                                  │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

    ┌──────────────────────┐
    │ user_activity_log    │
    ├──────────────────────┤
    │ id (PK, BIGSERIAL)   │
    │ user_id (FK, opt)    │
    │ action               │
    │ entity_type          │
    │ entity_id            │
    │ ip_address           │
    │ user_agent           │
    │ metadata (JSONB)     │
    │ created_at           │
    └──────────────────────┘


    ┌──────────────────────┐         ┌──────────────────────┐
    │  course_analytics    │         │   lab_analytics      │
    ├──────────────────────┤         ├──────────────────────┤
    │ id (PK, UUID)        │         │ id (PK, UUID)        │
    │ course_id (FK)       │         │ lab_id (FK)          │
    │ date                 │         │ date                 │
    │ views                │         │ starts               │
    │ enrollments          │         │ completions          │
    │ completions          │         │ avg_time_minutes     │
    │ avg_time_minutes     │         │ success_rate         │
    │ drop_off_rate        │         └──────────────────────┘
    └──────────────────────┘


┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                          KEY RELATIONSHIPS SUMMARY                                                               │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

    users ─────┬──── 1:N ──── user_roles ──── N:1 ──── roles
               │
               ├──── 1:N ──── user_sessions
               │
               ├──── 1:N ──── user_xp_summary
               │
               ├──── 1:N ──── course_enrollments ──── N:1 ──── courses
               │
               ├──── 1:N ──── lesson_progress ──── N:1 ──── lessons
               │
               ├──── 1:N ──── quiz_attempts ──── N:1 ──── quizzes
               │
               ├──── 1:N ──── exam_results ──── N:1 ──── quizzes
               │
               ├──── 1:N ──── lab_instances ──── N:1 ──── labs
               │
               ├──── 1:N ──── submissions ──── N:1 ──── challenges
               │
               ├──── 1:N ──── xp_history
               │
               ├──── 1:N ──── user_badges ──── N:1 ──── badges
               │
               ├──── 1:N ──── certificates
               │
               ├──── 1:N ──── payments
               │
               └──── 1:N ──── notifications


    courses ─────┬──── 1:N ──── modules ──── 1:N ──── lessons
                 │
                 ├──── 1:N ──── quizzes
                 │
                 └──── N:1 ──── course_categories


    labs ─────┬──── 1:N ──── machines
              │
              ├──── 1:N ──── lab_instances
              │
              └──── 1:N ──── challenges ──── 1:N ──── flags ──── 1:N ──── submissions


┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                          PHYSICAL RELATIONSHIP DIAGRAM                                                           │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘


                    ┌─────────────┐
                    │    roles    │
                    └──────┬──────┘
                           │
                    ┌──────▼──────┐
                    │role_perms   │
                    └──────┬──────┘
                           │
        ┌──────────────────▼──────────────────┐
        │              users                   │
        └──────────────────┬──────────────────┘
                           │
     ┌─────────────────────┼─────────────────────┬─────────────────────┐
     │                     │                     │                     │
┌────▼────┐          ┌─────▼─────┐         ┌─────▼─────┐         ┌─────▼─────┐
│user_roles│         │sessions   │         │xp_summary │         │notifications│
└─────────┘          └───────────┘         └───────────┘         └───────────┘
                           │
     ┌─────────────────────┼─────────────────────┬─────────────────────┐
     │                     │                     │                     │
┌────▼────┐          ┌─────▼─────┐         ┌─────▼─────┐         ┌─────▼─────┐
│enrollments│        │progress   │         │submissions│         │badges     │
└────┬────┘          └───────────┘         └───────────┘         └───────────┘
     │
┌────▼────┐
│courses  │
└────┬────┘
     │
┌────┼────────────┐
│    │             │
▼    ▼             ▼
┌────┐ ┌────┐ ┌────┐
│mods│ │quiz│ │labs│
└──┬─┘ └────┘ └──┬─┘
   │              │
▼              ▼
┌────┐        ┌────┐
│less│        │mach│
└──┬─┘        └────┘
   │
▼
┌────┐
│quiz│
└──┬─┘
   │
▼
┌────┐
│qstn│
└──┬─┘
   │
▼
┌────┐
│answ│
└────┘
