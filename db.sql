create table if not exists premium_plans
(
    id              int auto_increment
        primary key,
    code            varchar(50)   not null,
    name            varchar(100)  not null,
    price           int           not null,
    duration        varchar(50)   not null,
    info            varchar(255)  null,
    features        longtext      null,
    duration_months int default 0 not null,
    constraint code
        unique (code),
    check (json_valid(`features`))
)
    collate = utf8mb4_czech_ci;

create table if not exists users
(
    id               int auto_increment
        primary key,
    username         varchar(32)                            not null,
    password         varchar(128)                           not null,
    role             varchar(40)                            not null,
    authtoken        varchar(100)                           null,
    last_login       datetime   default current_timestamp() null,
    email            varchar(128)                           null,
    is_premium       tinyint(1) default 0                   not null,
    premium_duration datetime                               null,
    constraint authtoken
        unique (authtoken)
)
    charset = utf8mb4;

create table if not exists messages
(
    id                int auto_increment
        primary key,
    sender_id         int                                    not null,
    receiver_id       int                                    not null,
    content           text                                   null,
    encrypted_content longtext                               null,
    encryption_iv     varchar(255)                           null,
    encryption_tag    varchar(255)                           null,
    is_read           tinyint(1) default 0                   null,
    created_at        datetime   default current_timestamp() null,
    constraint fk_msg_receiver
        foreign key (receiver_id) references users (id)
            on delete cascade,
    constraint fk_msg_sender
        foreign key (sender_id) references users (id)
            on delete cascade
)
    collate = utf8mb4_unicode_ci;

create index if not exists idx_encrypted_content
    on messages (encrypted_content(100));

create index if not exists receiver_id
    on messages (receiver_id);

create index if not exists sender_id
    on messages (sender_id);

create table if not exists orders
(
    id              int auto_increment
        primary key,
    order_number    varchar(50)                          not null,
    item            varchar(100)                         not null,
    price           float                                not null,
    duration_months int      default 0                   not null,
    created_at      datetime default current_timestamp() not null,
    user_id         int                                  null,
    duration        int      default 0                   not null,
    constraint order_number
        unique (order_number),
    constraint fk_userID
        foreign key (user_id) references users (id)
)
    collate = utf8mb4_czech_ci;

create table if not exists posts
(
    id         int auto_increment
        primary key,
    title      varchar(255)                           not null,
    content    text                                   not null,
    created_at timestamp  default current_timestamp() not null,
    user_id    int                                    null,
    image      varchar(255)                           null,
    likes      int        default 0                   not null,
    is_premium tinyint(1) default 0                   not null,
    constraint fk_posts_user
        foreign key (user_id) references users (id)
            on update cascade on delete set null
)
    charset = utf8mb4;

create table if not exists comments
(
    id         int auto_increment
        primary key,
    post_id    int                                   not null,
    name       varchar(250)                          not null,
    email      varchar(250)                          not null,
    content    text                                  not null,
    created_at timestamp default current_timestamp() not null,
    user_id    int                                   null,
    parent_id  int                                   null,
    constraint comments_ibfk_1
        foreign key (post_id) references posts (id),
    constraint fk_comment_parent
        foreign key (parent_id) references comments (id)
            on delete cascade,
    constraint fk_comments_user
        foreign key (user_id) references users (id)
            on update cascade on delete set null
)
    charset = utf8;

create table if not exists comment_likes
(
    comment_id int not null,
    user_id    int not null,
    primary key (comment_id, user_id),
    constraint comment_likes_ibfk_1
        foreign key (comment_id) references comments (id)
            on delete cascade,
    constraint comment_likes_ibfk_2
        foreign key (user_id) references users (id)
            on delete cascade
)
    charset = utf8mb4;

create index if not exists user_id
    on comment_likes (user_id);

create table if not exists comment_reactions
(
    id         int auto_increment
        primary key,
    comment_id int          not null,
    user_id    int          not null,
    emoji      varchar(191) not null,
    constraint comment_user_emoji
        unique (comment_id, user_id, emoji),
    constraint comment_reactions_ibfk_1
        foreign key (comment_id) references comments (id)
            on delete cascade,
    constraint comment_reactions_ibfk_2
        foreign key (user_id) references users (id)
            on delete cascade
)
    collate = utf8mb4_unicode_ci;

create index if not exists user_id
    on comment_reactions (user_id);

create index if not exists post_id
    on comments (post_id);

create table if not exists post_likes
(
    post_id int not null,
    user_id int not null,
    primary key (post_id, user_id),
    constraint post_likes_ibfk_1
        foreign key (post_id) references posts (id)
            on delete cascade,
    constraint post_likes_ibfk_2
        foreign key (user_id) references users (id)
            on delete cascade
)
    charset = utf8mb4;

create index if not exists user_id
    on post_likes (user_id);

create table if not exists post_reactions
(
    id      int auto_increment
        primary key,
    post_id int          not null,
    user_id int          not null,
    emoji   varchar(191) not null,
    constraint post_user_emoji
        unique (post_id, user_id, emoji),
    constraint post_reactions_ibfk_1
        foreign key (post_id) references posts (id)
            on delete cascade,
    constraint post_reactions_ibfk_2
        foreign key (user_id) references users (id)
            on delete cascade
)
    collate = utf8mb4_unicode_ci;

create index if not exists user_id
    on post_reactions (user_id);


