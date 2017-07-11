# Database

## loft_core_users

This table augments the `users` table.  By design, when a user is deleted, their associated record in this table remains; this is because thier information may be used to fight SPAM.

### `loft_core_users.status`

This column shows if a user has been blocked as a robotrap.
