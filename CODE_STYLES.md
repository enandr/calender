# Code Styles

As powerful as _writing_ code can be, programmers usually spend much more time _reading_ code than writing it. Some say 10x more time. This is because beyond a certain size, new functionality is usually added to a system by _modifying existing code_ rather than _writing new code_. And in order to modify code without breaking it, we must first understand it. This is where all of the reading comes from.

Although computers don't care about code style, it is important to remember that code is not written for computers. Code is written for humans. Machines are actually built to execute instructions that do not resemble natural language at all. Programming languages are an attempt at giving humans the ability to express ideas and business processes in a way that computers can execute them.

Teams that write code with readers (each other) in mind will always have an easier time adding functionality and fixing bugs than those that don't. An easy first step toward writing readable code is consistency. It's much easier to read code if its style is predictable. The reader is not distracted by small variations in the code while they are trying to understand its intent.

This guide outlines just a few rules that your team can follow to help boost the overall readability of a project. These rules are not set in stone and the code police will not come after you. But the important thing is adhering to _some_ style.

## File and Directory Names

File and directory names should be written in `kebab-case`. That is all lowercase, dash-separated. Mac OS and Linux use case-sensitive file systems, but Windows does not. This convention helps mitigate some nasty issues with Git and surprise errors when importing modules.

```bash
# no ❌

Client
├── components
│   ├── App.jsx
│   ├── cartsummaryitem.jsx
│   ├── cartSummary.jsx
│   ├── checkout.jsx
│   ├── Header.jsx
│   ├── product_details.jsx
│   ├── Product-List-Item.jsx
│   └── product list.jsx
├── index.jsx
└── LIB
    ├── getCartTotal.js
    ├── index.js
    ├── json.js
    └── ToDollars.js

# yes ✅

client
├── components
│   ├── app.jsx
│   ├── cart-summary-item.jsx
│   ├── cart-summary.jsx
│   ├── checkout.jsx
│   ├── header.jsx
│   ├── product-details.jsx
│   ├── product-list-item.jsx
│   └── product-list.jsx
├── index.jsx
└── lib
    ├── get-cart-total.js
    ├── index.js
    ├── json.js
    └── to-dollars.js
```

## SQL

### Table Names

Name SQL tables using plural nouns, written in `camelCase`.

```sql
-- no ❌

SELECT *
  FROM `CartItem`;

-- no ❌

SELECT *
  FROM `cart_items`;

-- yes ✅

SELECT *
  FROM `cartItems`;
```

### Column Names

Name SQL columns using `camelCase`.

```sql
-- no ❌

SELECT `MessageID`,
       `SentAt`
  FROM `Message`;

-- no ❌

SELECT `message_id`,
       `sent_at`
  FROM `messages`;

-- yes ✅

SELECT `messageId`,
       `sentAt`
  FROM `messages`;
```

### Primary Keys

Avoid using just `id` as a column name. This helps simplify joins a bit when a primary key matches a foreign key.

```sql
-- no ❌

SELECT `u`.`id`,
       `u`.`username`,
       `p`.`url`
  FROM `users` AS `u`
  JOIN `photos` AS `p` ON `u`.`id` = `p`.`userId`;

-- yes ✅

SELECT `u`.`userId`,
       `u`.`username`,
       `p`.`url`
  FROM `users` AS `u`
  JOIN `photos` AS `p` USING (`userId`);
```

### Boolean Flags

Prefer an `is` prefix on Boolean columns.

```sql
-- no ❌

UPDATE `friendRequests`
   SET `accepted` = true
 WHERE `friendRequestId` = 1;

-- yes ✅

UPDATE `friendRequests`
   SET `isAccepted` = true
 WHERE `friendRequestId` = 1;
```

## PHP

### Function and Variable Names

Function and variable names should be written in `snake_case`.

Function names should contain a verb or verb-noun combo.

```php
// no ❌

function validID($id, $message) {
  $asInt = intval($id);
  if ($asInt <= 0) {
    throw new ApiError($message, 400);
  }
  return $asInt;
}

// yes ✅

function validate_id($id, $message) {
  $as_int = intval($id);
  if ($as_int <= 0) {
    throw new ApiError($message, 400);
  }
  return $as_int;
}
```

### Indent SQL Strings

SQL should be carefully formatted just like other code. PHP supports mulit-line strings.

```php
// no ❌

function get_user($link, $user_id) {
  $sql = "SELECT `userId`, `username` FROM `users` WHERE `userId` = $user_id AND `isActive` = true";
  $result = $link->query($sql);
  $users = $result->fetch_all(MYSQLI_ASSOC);
  return $users;
}

// yes ✅

function get_user($link, $user_id) {
  $sql = "
    SELECT `userId`,
           `username`
      FROM `users`
     WHERE `userId`   = $user_id
       AND `isActive` = true
  ";
  $result = $link->query($sql);
  $users = $result->fetch_all(MYSQLI_ASSOC);
  return $users;
}
```

## HTML and CSS

### Class Names

Use `kebab-case` for class names.

```css
/* no ❌ */

.CallToAction {
  background-color: green;
}

/* no ❌ */

.callToAction {
  background-color: green;
}

/* yes ✅ */

.call-to-action {
  background-color: green;
}
```

### `id` Attributes

Use `kebab-case` for `id` attributes.

```html
<!-- no ❌ -->

<p id="LongDescription">
  Intertia is a property of matter.
</p>

<!-- no ❌ -->

<p id="longDescription">
  Intertia is a property of matter.
</p>

<!-- yes ✅ -->

<p id="long-description">
  Intertia is a property of matter.
</p>
```
