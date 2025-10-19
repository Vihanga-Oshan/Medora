/* ===== form-validation.js (fixed) ===== */
console.log("[validator] loaded");

/* helpers */
function normalizeNIC(s){ return (s||"").replace(/[\s-]+/g,"").toUpperCase().trim(); }
function normalizePhone(s){ return (s||"").replace(/[\s-]+/g,"").trim(); }
function nicOK(v){ v = normalizeNIC(v); return /^\d{12}$/.test(v) || /^\d{9}[VvXx]$/.test(v); }
function phoneOK(v){ v = normalizePhone(v); return /^0\d{9}$/.test(v) || /^(?:\+94|94)\d{9}$/.test(v); }

function setError(input, msg){
    if(!input) return;
    input.classList.add("invalid");
    input.setAttribute("aria-invalid","true");
    let hint = input.parentElement.querySelector(".input-error");
    if(!hint){
        hint = document.createElement("small");
        hint.className = "input-error";
        hint.style.color = "#e11d48";
        hint.style.display = "block";
        hint.style.marginTop = "4px";
        input.parentElement.appendChild(hint);
    }
    hint.textContent = msg || "";
}
function clearError(input){
    if(!input) return;
    input.classList.remove("invalid");
    input.setAttribute("aria-invalid","false");
    const hint = input.parentElement.querySelector(".input-error");
    if(hint) hint.textContent = "";
}

/* ---------- Guardian Register ---------- */
window.guardianValidate = function(form){
    const gname = form.querySelector('input[name="g_name"]');
    const nic   = form.querySelector('input[name="nic"]');
    const phone = form.querySelector('input[name="contact_number"]');
    const email = form.querySelector('input[name="email"]');
    const pw    = form.querySelector('input[name="password"]');
    const agree = form.querySelector('input[name="agree"]');

    [gname,nic,phone,email,pw,agree].forEach(i=>{
        if(!i) return;
        const ev = i.type === "checkbox" ? "change" : "input";
        i.addEventListener(ev, ()=> clearError(i), {once:true});
    });

    let ok = true;

    if(!gname || !gname.value.trim()){ setError(gname,"User name is required"); ok = false; }
    if(!nic || !nicOK(nic.value)){ setError(nic,"NIC must be 12 digits or 9 digits + V/X"); ok = false; }
    if(!phone || !phoneOK(phone.value)){ setError(phone,"Phone like 0XXXXXXXXX or +94XXXXXXXXX"); ok = false; }
    if(email && email.value && !email.checkValidity()){ setError(email,"Enter a valid email"); ok = false; }
    if(!pw || pw.value.length < 6){ setError(pw,"Password min 6 chars"); ok = false; }
    if(!agree || !agree.checked){ setError(agree,"You must agree"); ok = false; }

    if(!ok) return false;

    // normalize before sending
    nic.value   = normalizeNIC(nic.value);
    phone.value = normalizePhone(phone.value);
    return true;
};

/* ---------- Patient Register (fixed for 2-step) ---------- */
window.patientValidate = function(form, step1Only = false){
    const name  = form.querySelector('input[name="name"]');
    const phone = form.querySelector('input[name="emergencyContact"]');
    const nic   = form.querySelector('input[name="nic"]');
    const email = form.querySelector('input[name="email"]');
    const pw    = form.querySelector('input[name="password"]');
    const gNic  = form.querySelector('input[name="guardianNic"]');
    const privacy = form.querySelector('input[name="privacy"]');
    const allergies = form.querySelector('textarea[name="allergies"]');
    const chronic = form.querySelector('textarea[name="chronic"]');

    [name,phone,nic,email,pw,gNic,privacy,allergies,chronic].forEach(i=>{
        if(i) i.addEventListener("input", ()=> clearError(i), {once:true});
    });

    let ok = true;

    // STEP 1 checks
    if(!name || !name.value.trim()){ setError(name,"Full name is required"); ok = false; }
    if(!phone || !phoneOK(phone.value)){ setError(phone,"Phone like 0XXXXXXXXX or +94XXXXXXXXX"); ok = false; }
    if(!nic || !nicOK(nic.value)){ setError(nic,"NIC must be 12 digits or 9 digits + V/X"); ok = false; }
    if(email && email.value && !email.checkValidity()){ setError(email,"Enter a valid email"); ok = false; }
    if(!pw || pw.value.length < 6){ setError(pw,"Password min 6 chars"); ok = false; }

    if(step1Only) return ok; // stop here when user clicks "Next"

    // STEP 2 checks (only runs on final submit)
    if(gNic && gNic.value.trim()){
        if (!nicOK(gNic.value)) { setError(gNic, "Guardian NIC is invalid"); ok = false; }
        else if (normalizeNIC(gNic.value) === normalizeNIC(nic.value)) {
            setError(gNic, "Guardian NIC cannot be same as patient NIC"); ok = false;
        }
    }

    if(!privacy || !privacy.checked){
        setError(privacy,"You must agree to the Privacy Policies");
        ok = false;
    }

    if(!ok) return false;

    nic.value   = normalizeNIC(nic.value);
    phone.value = normalizePhone(phone.value);
    return true;
};


/* ---------- Logins (patient & guardian) ---------- */
window.loginValidate = function(form){
    const nic = form.querySelector('input[name="nic"]');
    const pw  = form.querySelector('input[name="password"]');

    [nic,pw].forEach(i=> i && i.addEventListener("input", ()=> clearError(i), {once:true}));

    let ok = true;

    if(!nic || !nicOK(nic.value)){ setError(nic,"Enter a valid NIC"); ok = false; }
    if(!pw || pw.value.length < 6){ setError(pw,"Password min 6 characters"); ok = false; }

    if(!ok) return false;

    nic.value = normalizeNIC(nic.value);
    return true;
};

/* auto-wire (keeps inline onsubmit as fallback) */
document.addEventListener("DOMContentLoaded", function(){
    console.log("[validator] DOM ready");

    const gf = document.getElementById("guardianForm") ||
        document.querySelector('form[action*="/register/guardian"]');
    if(gf){
        gf.addEventListener("submit", function(e){
            if(!window.guardianValidate(gf)){
                e.preventDefault();
                const first = gf.querySelector(".invalid");
                if(first && first.scrollIntoView) first.scrollIntoView({behavior:"smooth", block:"center"});
            }
        });
        console.log("[validator] guardian wired");
    }

    const pf = document.getElementById("patientForm") ||
        document.querySelector('form[action*="/register/patient"]');
    if(pf){
        pf.addEventListener("submit", function(e){
            if(!window.patientValidate(pf)){
                e.preventDefault();
                const first = pf.querySelector(".invalid");
                if(first && first.scrollIntoView) first.scrollIntoView({behavior:"smooth", block:"center"});
            }
        });
        console.log("[validator] patient wired");
    }

    document.querySelectorAll('form[action$="/login"], form[action$="/loginguardian"]').forEach(f=>{
        f.addEventListener("submit", function(e){
            if(!window.loginValidate(f)){
                e.preventDefault();
                const first = f.querySelector(".invalid");
                if(first && first.scrollIntoView) first.scrollIntoView({behavior:"smooth", block:"center"});
            }
        });
    });
});