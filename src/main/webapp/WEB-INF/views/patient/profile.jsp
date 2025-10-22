<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Profile - Medora</title>

  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/patient/main.css">
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/components/header.css">
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/patient/profile.css">
</head>
<body>
<jsp:include page="/WEB-INF/views/components/header.jsp" />

<main class="container">
  <h1 class="section-title">My Profile</h1>
  <p class="section-subtitle">Manage your personal information</p>

  <div class="card">
    <div class="profile-header">
      <div>
        <h2 class="card-title">Personal Information</h2>
        <p class="card-subtitle">Update your profile details</p>
      </div>
      <div class="avatar">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="10" fill="#0096FF"/>
          <path d="M12 16C14.2091 16 16 14.2091 16 12C16 9.79086 14.2091 8 12 8C9.79086 8 8 9.79086 8 12C8 14.2091 9.79086 16 12 16Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M12 16V18" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
    </div>

    <form class="profile-form" action="${pageContext.request.contextPath}/patient/profile" method="post">
      <div class="form-row">
        <div class="form-group">
          <label>First Name</label>
          <input type="text" name="firstName" placeholder="First Name" />
        </div>
        <div class="form-group">
          <label>Last Name</label>
          <input type="text" name="lastName"  placeholder="Last Name" />
        </div>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email"  placeholder="Email" disabled />
        <small>Email cannot be changed</small>
      </div>

      <div class="form-group">
        <label>Phone Number</label>
        <input type="tel" name="phone" placeholder="Phone Number" />
      </div>

      <div class="form-group">
        <label>Date of Birth</label>
        <input type="text" name="dob"  placeholder="mm/dd/yyyy" />
      </div>

      <div class="form-group">
        <label>Address</label>
        <input type="text" name="address"  placeholder="Enter your address" />
      </div>

      <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
  </div>
</main>

<jsp:include page="/WEB-INF/views/components/footer.jsp" />
</body>
</html>
