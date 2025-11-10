<br />
<div align="center">
  <a href="https://github.com/fkhrlhilmi/BookHeaven-PurchasingSystem/tree/main">
    <img src="images/logo.png" alt="Logo" width="350" height="350">
  </a>

# BHPS - Book Heaven Purchasing System

</div>

Book Heaven Purchasing System (BHPS) is an online book purchasing platform designed for customers, staff, and admins. Customers can browse and buy books seamlessly, staff manage orders and inventory, and admins oversee the entire platform, including user management and reporting. This project is primarily for educational purposes and demonstrates full-stack web development skills.


<details>
  <summary>Table of Contents</summary>
  <ol>
    <li><a href="#screenshots">Screenshots</a></li>
    <li><a href="#features">Features</a></li>
    <li><a href="#technologies-used">Technologies Used</a></li>
    <li><a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li><a href="#usage">Usage</a></li>
    <li><a href="#contribution">Contribution</a></li>
    <li><a href="#license">License</a></li>
    <li><a href="#acknowledgments">Acknowledgments</a></li>
  </ol>
</details>


## Screenshots

### Web Screenshots

<table>
  <tr>
    <td align="center"><strong>Sign In</strong></td>
    <td align="center"><strong>Main Page</strong></td>
  </tr>
  <tr>
    <td><img src="images/SignInPage.jpg" alt="Web - SignIn" width="300"></td>
    <td><img src="images/MainPage.jpg" alt="Web - Home" width="300"></td>
  </tr>
  <tr>
    <td align="center"><strong>Book Detail Page</strong></td>
    <td align="center"><strong>Checkout Page</strong></td>
  </tr>
  <tr>
    <td><img src="images/BookDetail.jpg" alt="Web - Book Detail" width="300"></td>
    <td><img src="images/AdminPortal.jpg" alt="Web - Admin" width="300"></td>
  </tr>
  <tr>
    <td align="center"><strong>Order Page</strong></td>
    <td align="center"><strong>Staff Page</strong></td>
  </tr>
  <tr>
    <td><img src="images/Order.jpg" alt="Web - Order" width="300"></td>
    <td><img src="images/StaffPortal.jpg" alt="Web - Staff" width="300"></td>
  </tr>
  <tr>
    <td align="center"><strong>Admin Page</strong></td>
  </tr>
  <tr>
    <td><img src="images/AdminPortal.jpg" alt="Web - Admin" width="300"></td>
  </tr>
</table>


## Features

* **Registration & Login:** Quick sign-up and login for customers, staff, and admins.
* **Book Management:** Staff can add, update, and delete books.
* **Order & Payment Management:** Customers can make orders and pay securely. Customers can view and cancel orders; staff can process orders and update statuses. The system supports multiple payment methods and generates email receipts.
* **Reporting:** Admins generate sales reports; staff can view and download reports to track performance.


## Technologies Used

* **Web Server:** XAMPP (Apache) [Installation Guide](https://www.apachefriends.org/index.html)
* **Database:** MySQL (via phpMyAdmin)
* **Frontend:** HTML, CSS, JavaScript, Bootstrap
* **Backend:** PHP
* **Framework:** Next.js
* **Version Control:** GitHub / GitHub Desktop
* **Browser Compatibility:** Google Chrome, Microsoft Edge, and other modern browsers



## Getting Started

### Prerequisites

* **XAMPP (Apache & MySQL)** installed for running the server and database.
* **VS Code (Optional):** Recommended for development, with **PHP Intelephense** and **PHP Server** extensions installed.

### Installation

1. Clone this repository:

   ```bash
   git clone https://github.com/fkhrlhilmi/BookHeaven-PurchasingSystem.git
   ```
2. Move the project folder to your XAMPP `htdocs` directory.
3. Open phpMyAdmin and import `database.sql` from the `db` folder.
4. Start Apache and MySQL in XAMPP.
5. Open your browser and navigate to:

   ```
   http://localhost/BookHeaven-PurchasingSystem
   ```


## Usage

* **Customers:** Register → Browse books → Add to cart → Checkout → View/cancel orders
* **Staff:** Login → Manage books → Process orders
* **Admin:** Login → Manage users → Generate reports → Oversee platform

---

## Contribution

Thank you to the team that contributed to the development of this website!

<a href="https://github.com/fkhrlhilmi/BookHeaven-PurchasingSystem/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=fkhrlhilmi/BookHeaven-PurchasingSystem" />
</a>


## License

This project uses multiple open-source licenses depending on the technologies used:

* **Apache License 2.0 (Apache-2.0)** — main backend logic and server-side scripts.
* **Mozilla Public License 2.0 (MPL-2.0)** — parts of the project using shared or modified open-source components.
* **Open Software License 3.0 (OSL-3.0)** — general project distribution and collaborative work.
* HTML, CSS, JavaScript, and MySQL — under their respective open/free usage terms.

This project follows open-source principles and is distributed for educational and non-commercial use.


## Acknowledgments

* **VS Code:** For providing excellent PHP development tools.
* **XAMPP (Apache):** For phpMyAdmin and server tools to develop the database.
* **Bootstrap:** For responsive and modern UI components.


