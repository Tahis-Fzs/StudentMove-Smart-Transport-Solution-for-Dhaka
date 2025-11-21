# StudentMove – Smart Transport Solution for Dhaka City Students

StudentMove is a full-stack smart transportation platform designed for students in Dhaka City.  
It enables real-time bus tracking, intelligent route suggestions, subscription-based transport services, admin monitoring, driver tracking, and automated notifications.

This project is collaboratively built by **5 team members**, where each member develops a **dedicated full-stack module**.

---

## 🚀 Features

### 🔹 Authentication
- Student registration & login  
- Email + OTP verification  
- Secure session tokens  
- Password reset  
- Profile management  

### 🔹 Smart Routes & Real-Time Tracking
- View all routes  
- Google Maps integration  
- Live bus GPS tracking (updates every 10 seconds)  
- ETA predictions  
- Route ranking & suggestions  

### 🔹 Subscription & Payment
- Multiple subscription plans  
- Payment gateway support  
- Invoice generation  
- Subscription history  

### 🔹 Notification System
- Push + email notifications  
- Delay alerts  
- Route change alerts  
- Notification preferences  

### 🔹 Feedback System
- Submit feedback  
- Admin replies  
- Feedback history  

### 🔹 Admin Dashboard
- Manage buses, drivers, routes  
- Analytics & reports  
- User suspension system  
- GPS override controls  

### 🔹 Driver App
- Driver login  
- GPS updates every 10 seconds  
- Bus status updates  

---

# 🧩 Full Functional Requirements Distribution

### ✔ FR-30 and FR-31 are removed

---

## 👨‍💻 **Md. Shadman Hasin — ID: 0242220005101462**  
### Module: Authentication (FR-1 to FR-8)
- FR-1: User Registration  
- FR-2: Student ID + Email Validation  
- FR-3: OTP / Email Verification  
- FR-4: User Login  
- FR-5: Forgot Password  
- FR-6: Profile Update  
- FR-7: Password Encryption  
- FR-8: Secure Session Tokens  

---

## 👨‍💻 **Md. Shadman Tahsin — ID: 0242220005101461**  
### Module: Routes & Real-Time Tracking (FR-9 to FR-17)
- FR-9: View All Routes  
- FR-10: Google Maps API Integration  
- FR-11: Bus GPS Fetching (10s refresh)  
- FR-12: Bus Location + ETA  
- FR-13: Route Suggestion  
- FR-14: Ranked Route Options  
- FR-15: Delay Alerts  
- FR-16: Next Bus Display  
- FR-17: Save Favorite Routes  

---

## 👨‍💻 **Md. Julfikar Hasan — ID: 0242220005101495**  
### Module: Subscription & Payment (FR-18 to FR-25)
- FR-18: Subscription Plans  
- FR-19: Plan Details  
- FR-20: Payment Gateway  
- FR-21: Invoice Generation  
- FR-22: Payment Confirmation  
- FR-23: Subscription Status Update  
- FR-24: Subscription History  
- FR-25: Transaction Storage  

---

## 👨‍💻 **Nahid Hasan — ID: 0242220005101460**  
### Module: Notification & Feedback (FR-26 to FR-29, FR-32 to FR-35)
- FR-26: Real-time Notifications  
- FR-27: FCM + Email Notifications  
- FR-28: Notification Preferences  
- FR-29: Notification List  
- FR-32: Submit Feedback  
- FR-33: Feedback Email Confirmation  
- FR-34: Admin Response  
- FR-35: Feedback Archiving  

---

## 👨‍💻 **KM Najimuddin — ID: 0242220005101493**  
### Module: Admin Dashboard & Driver App (FR-36 to FR-45)
- FR-36: Admin Dashboard  
- FR-37: CRUD for Bus/Driver/Route  
- FR-38: GPS Override  
- FR-39: Reports (Daily/Weekly/Monthly)  
- FR-40: User Suspension  
- FR-41: Admin Logs  
- FR-42: Driver Login  
- FR-43: Driver GPS Updates  
- FR-44: Bus Status Update  
- FR-45: Admin Status Override  

---

# 🏗️ Tech Stack

### **Backend:** Laravel 10 (PHP 8+)  
### **Frontend:** Blade, TailwindCSS, Vite  
### **Database:** MySQL  
### **APIs:** Google Maps, Firebase Cloud Messaging, Payment Gateway, GPS Driver App

---

# 🔧 Installation Guide

### 1️⃣ Clone the Repository
```bash
git clone https://github.com/<your-repo>/StudentMove.git
cd StudentMove