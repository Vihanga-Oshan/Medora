<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<%--
  Created by IntelliJ IDEA.
  User: User
  Date: 9/1/2025
  Time: 7:52 PM
  To change this template use File | Settings | File Templates.
--%>
<%@ page contentType="text/html;charset=UTF-8" language="java" %>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/patient/dashstyle.css">

    <style>
        a,
        button,
        input,
        select,
        h1,
        h2,
        h3,
        h4,
        h5,
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            border: none;
            text-decoration: none;
            background: none;

            -webkit-font-smoothing: antialiased;
        }

        menu, ol, ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }
    </style>
    <title>Dashboard</title>
</head>
<body>
<div class="patient-dashboard">
    <div class="depth-0-frame-0">
        <div class="rect"></div>
        <div class="depth-1-frame-0">
            <div class="depth-2-frame-1">
                <div class="depth-4-frame-0">
                    <div class="depth-5-frame-0">
                        <div class="depth-6-frame-0">
                            <img class="depth-7-frame-0" src="${pageContext.request.contextPath}/assets/images-patient/depth-7-frame-00.png" />
                            <div class="depth-7-frame-1">
                                <div class="depth-8-frame-0">
                                    <div class="emily-carter">Emily Carter</div>
                                </div>
                                <div class="depth-8-frame-1">
                                    <div class="patient-id-12345-age-30-conditions-none">
                                        Patient ID: 12345, Age: 30, Conditions: None
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="depth-4-frame-1">
                    <div class="depth-5-frame-02">
                        <div class="depth-6-frame-02">
                            <div class="welcome-emily">Welcome Emily!</div>

                        </div>
                        <div class="depth-6-frame-1">
                            <div class="here-s-your-medication-schedule-for-today">
                                Here&#039;s your medication schedule for today.
                            </div>

                        </div>

                    </div>
                </div>
                <div class="depth-4-frame-2">

                    <div class="medication-timetable">Medication Timetable</div>
                    <!-- ✅ UPLOAD BUTTON ADDED HERE -->
                    <div style="margin-left:350px;">
                        <a href="${pageContext.request.contextPath}/upload-prescription"
                           style="background-color: #007acc; color: white; padding: 8px 20px; border-radius: 20px; text-decoration: none; font-family: 'Lexend', sans-serif; font-weight: bold; font-size: 15px; display:block;">
                            Upload New Prescription
                        </a>
                    </div>
                </div>

                <div class="depth-4-frame-4">
                    <div class="notifications">Notifications</div>
                </div>
                <div class="frame-1000001127">
                    <div class="depth-4-frame-5">
                        <div class="depth-5-frame-03">
                            <div class="depth-6-frame-03">
                                <img class="vector-0" src="${pageContext.request.contextPath}/assets/images-patient/vector-00.svg" />
                                <div class="depth-7-frame-02"></div>
                            </div>
                        </div>
                        <div class="depth-5-frame-1">
                            <div class="depth-6-frame-04">
                                <div class="reminder-take-medication-b">
                                    Reminder: Take Medication B
                                </div>
                            </div>
                            <div class="depth-6-frame-12">
                                <div class="_2-hours-ago">2 hours ago</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="frame-1000001128">
                    <div class="depth-4-frame-5">
                        <div class="depth-5-frame-03">
                            <div class="depth-6-frame-03">
                                <img class="vector-02" src="${pageContext.request.contextPath}/assets/images-patient/vector-01.svg" />
                                <div class="depth-7-frame-02"></div>
                            </div>
                        </div>
                        <div class="depth-5-frame-1">
                            <div class="depth-6-frame-04">
                                <div class="reminder-take-medication-b">
                                    Reminder: Take Medication B
                                </div>
                            </div>
                            <div class="depth-6-frame-12">
                                <div class="_2-hours-ago">2 hours ago</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="depth-4-frame-7">
                    <div class="adherence-history">Adherence History</div>
                </div>
                <div class="depth-4-frame-8">
                    <div class="depth-5-frame-04">
                        <div class="depth-6-frame-05">
                            <div class="medication-adherence">Medication Adherence</div>
                        </div>
                        <div class="depth-6-frame-13">
                            <div class="_90">90%</div>
                        </div>
                        <div class="depth-6-frame-2">
                            <div class="depth-7-frame-03">
                                <div class="last-30-days">Last 30 Days</div>
                            </div>
                            <div class="depth-7-frame-12">
                                <div class="_5">+5%</div>
                            </div>
                        </div>
                        <div class="depth-6-frame-3">
                            <div class="depth-7-frame-04">
                                <div class="depth-8-frame-02"></div>
                                <div class="depth-8-frame-12">
                                    <div class="week-1">Week 1</div>
                                </div>
                            </div>
                            <div class="depth-7-frame-13">
                                <div class="depth-8-frame-02"></div>
                                <div class="depth-8-frame-12">
                                    <div class="week-2">Week 2</div>
                                </div>
                            </div>
                            <div class="depth-7-frame-2">
                                <div class="depth-8-frame-02"></div>
                                <div class="depth-8-frame-12">
                                    <div class="week-3">Week 3</div>
                                </div>
                            </div>
                            <div class="depth-7-frame-3">
                                <div class="depth-8-frame-02"></div>
                                <div class="depth-8-frame-12">
                                    <div class="week-4">Week 4</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <img class="image-4" src="${pageContext.request.contextPath}/assets/images-patient/image-40.png" />
                <div class="depth-4-frame-3">
                    <div class="depth-5-frame-05">
                        <div class="depth-6-frame-06">
                            <!-- Table Header -->
                            <div class="depth-7-frame-05">
                                <div class="depth-8-frame-03">
                                    <div class="depth-9-frame-0">
                                        <div class="time">Time</div>
                                    </div>
                                    <div class="depth-9-frame-1">
                                        <div class="medication">Medication</div>
                                    </div>
                                    <div class="depth-9-frame-2">
                                        <div class="dosage">Dosage</div>
                                    </div>
                                    <div class="depth-9-frame-3">
                                        <div class="status">Status</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Table Rows -->
                            <div class="depth-7-frame-14">
                                <div class="depth-8-frame-04">
                                    <div class="depth-9-frame-02">
                                        <div class="_8-00-am">8:00 AM</div>
                                    </div>
                                    <div class="depth-9-frame-12">
                                        <div class="medication-a">Medication A</div>
                                    </div>
                                    <div class="depth-9-frame-22">
                                        <div class="_1-tablet">1 tablet</div>
                                    </div>
                                    <div class="depth-9-frame-32">
                                        <div class="depth-10-frame-0">
                                            <div class="depth-11-frame-0">
                                                <div class="taken">Taken</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="depth-8-frame-13">
                                    <div class="depth-9-frame-02">
                                        <div class="_12-00-pm">12:00 PM</div>
                                    </div>
                                    <div class="depth-9-frame-12">
                                        <div class="medication-b">Medication B</div>
                                    </div>
                                    <div class="depth-9-frame-22">
                                        <div class="_2-tablets">2 tablets</div>
                                    </div>
                                    <div class="depth-9-frame-32">
                                        <div class="depth-10-frame-02">
                                            <div class="depth-11-frame-0">
                                                <div class="missed">Missed</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="depth-8-frame-2">
                                    <div class="depth-9-frame-02">
                                        <div class="_6-00-pm">6:00 PM</div>
                                    </div>
                                    <div class="depth-9-frame-12">
                                        <div class="medication-c">Medication C</div>
                                    </div>
                                    <div class="depth-9-frame-22">
                                        <div class="_1-tablet">1 tablet</div>
                                    </div>
                                    <div class="depth-9-frame-32">
                                        <div class="depth-10-frame-0">
                                            <div class="depth-11-frame-0">
                                                <div class="taken">Taken</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="group-1000001054">
            <div class="depth-3-frame-0">
                <div class="depth-4-frame-02">
                    <img class="depth-4-frame-03" src="${pageContext.request.contextPath}/assets/images-patient/depth-4-frame-02.png" />
                </div>
                <div class="depth-4-frame-12">
                    <div class="medora">Medora</div>
                </div>
            </div>
            <div class="frame-5"></div>
            <div class="line-48"></div>
            <div class="all-rights-reserved-medora-com-terms-and-conditions-apply">
                All rights reserved ® medora.com | Terms and conditions apply!
            </div>
            <div class="frame-120">
                <img class="facebook" src="images/facebook0.svg" />
                <img class="instagram" src="images/instagram0.svg" />
                <img class="youtube" src="images/youtube0.svg" />
                <img class="linkedin" src="images/linkedin0.svg" />
                <img class="twitter" src="images/twitter0.svg" />
            </div>
        </div>
    </div>
    <div class="frame-1000001097">
        <div class="depth-3-frame-02">
            <img class="depth-4-frame-22" src="${pageContext.request.contextPath}/assets/images-patient/depth-4-frame-21.png" />
            <div class="depth-4-frame-13">
                <div class="medora2">Medora</div>
            </div>
        </div>
        <div class="frame-2">
            <div class="dashboard"><a href="patient-dashboard.jsp"> Dashboard </a></div>
            <div class="medications"><a href="medications.jsp"> Medications</a></div>
            <div class="history"><a href="history.html">History</a></div>
            <div class="settings"><a href="settings.html"> Settings</a></div>
        </div>
    </div>
</div>

</body>
</html>