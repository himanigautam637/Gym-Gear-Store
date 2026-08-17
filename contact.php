<?php

session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

$currentPage = 'contact';

$success = '';
$error = '';

$name = '';
$email = '';
$phone = '';
$subject = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name =
        trim($_POST['name'] ?? '');

    $email =
        trim($_POST['email'] ?? '');

    $phone =
        trim($_POST['phone'] ?? '');

    $subject =
        trim($_POST['subject'] ?? '');

    $message =
        trim($_POST['message'] ?? '');

    if (
        $name === '' ||
        $email === '' ||
        $subject === '' ||
        $message === ''
    ) {

        $error =
            'Please fill in all fields.';

    } elseif (
        !preg_match('/^[a-zA-Z\s]+$/', $name)
    ) {

        $error =
            'Name can only contain letters and spaces.';

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            'Please enter a valid email address.';

    } elseif (
        $phone !== '' &&
        !preg_match('/^[0-9]{7,15}$/', $phone)
    ) {

        $error =
            'Phone number can only contain digits (7 to 15 numbers).';

    } else {

        try {

            $stmt = $pdo->prepare("
                INSERT INTO contact_messages
                (full_name, email, phone, subject, message)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $name,
                $email,
                $phone,
                $subject,
                $message
            ]);

            $success =
                'Your message has been sent successfully.';

            $name = '';
            $email = '';
            $phone = '';
            $subject = '';
            $message = '';

        } catch (PDOException $e) {

            $error =
                'Unable to send your message. Please try again.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Contact Us | Gym Gear Store
    </title>

    <link
        rel="stylesheet"
        href="/Gym-Gear-Store/partials/site.css"
    >

    <style>

        .contact-wrap {
            max-width: 900px;
            margin: 0 auto;
            padding: 60px 25px 80px;
        }

        .contact-heading {
            text-align: center;
            margin-bottom: 35px;
        }

        .contact-heading .eyebrow {
            color: #3f73e8;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
        }

        .contact-heading h1 {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .contact-heading p {
            color: #91a4bd;
            font-size: 14px;
        }

        .contact-card {
            max-width: 700px;
            margin: auto;
            padding: 30px;
            background: #122a4a;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 14px;
        }

        .contact-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .contact-group {
            display: flex;
            flex-direction: column;
        }

        .contact-group.full {
            grid-column: 1 / -1;
        }

        .contact-group label {
            color: #d2dceb;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 7px;
        }

        .contact-group input,
        .contact-group textarea {
            width: 100%;
            padding: 12px 13px;
            background: #081729;
            color: #fff;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            outline: none;
            font-family: inherit;
        }

        .contact-group input {
            height: 45px;
        }

        .contact-group textarea {
            min-height: 130px;
            resize: vertical;
        }

        .contact-group input:focus,
        .contact-group textarea:focus {
            border-color: #3f73e8;
        }

        .contact-submit {
            grid-column: 1 / -1;
            height: 46px;
            border: none;
            border-radius: 8px;
            background: #3f73e8;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }

        .contact-submit:hover {
            background: #5685ed;
        }

        .contact-info {
            text-align: center;
            margin-top: 25px;
            color: #91a4bd;
            font-size: 13px;
        }

        .contact-info strong {
            color: #fff;
        }

        .contact-alert {
            max-width: 700px;
            margin: 0 auto 20px;
            padding: 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
        }

        .contact-success {
            background: rgba(76,175,80,0.12);
            border: 1px solid rgba(76,175,80,0.25);
            color: #73d27b;
        }

        .contact-error {
            background: rgba(239,83,80,0.12);
            border: 1px solid rgba(239,83,80,0.25);
            color: #ff7774;
        }

        @media(max-width:600px) {

            .contact-form {
                grid-template-columns: 1fr;
            }

            .contact-group.full,
            .contact-submit {
                grid-column: auto;
            }

            .contact-heading h1 {
                font-size: 32px;
            }

        }

    </style>

</head>

<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<main class="contact-wrap">

    <div class="contact-heading">

        <div class="eyebrow">
            Get In Touch
        </div>

        <h1>
            Contact Us
        </h1>

        <p>
            Have a question? Send us a message and
            our team will get back to you.
        </p>

    </div>


    <?php if ($success !== ''): ?>

        <div class="contact-alert contact-success">

            <?= htmlspecialchars($success) ?>

        </div>

    <?php endif; ?>


    <?php if ($error !== ''): ?>

        <div class="contact-alert contact-error">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <div class="contact-card">

        <form
            class="contact-form"
            method="POST"
        >

            <div class="contact-group">

                <label for="name">
                    Name
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?= htmlspecialchars($name) ?>"
                    pattern="[A-Za-z\s]+"
                    title="Only letters and spaces are allowed"
                    required
                >

            </div>


            <div class="contact-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($email) ?>"
                    required
                >

            </div>


            <div class="contact-group">

                <label for="subject">
                    Subject
                </label>

                <input
                    type="text"
                    id="subject"
                    name="subject"
                    value="<?= htmlspecialchars($subject) ?>"
                    required
                >

            </div>


            <div class="contact-group">

                <label for="phone">
                    Phone Number
                </label>

                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    value="<?= htmlspecialchars($phone) ?>"
                    pattern="[0-9]{7,15}"
                    title="Digits only, 7 to 15 numbers"
                    inputmode="numeric"
                >

            </div>


            <div class="contact-group full">

                <label for="message">
                    Message
                </label>

                <textarea
                    id="message"
                    name="message"
                    required
                ><?= htmlspecialchars($message) ?></textarea>

            </div>


            <button
                type="submit"
                class="contact-submit"
            >
                Send Message
            </button>

        </form>


        <div class="contact-info">

            Contact Us at:

            <strong>
                9826574985
            </strong>

        </div>

    </div>

</main>


<?php include __DIR__ . '/partials/footer.php'; ?>


</body>
</html>