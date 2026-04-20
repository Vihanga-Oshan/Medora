<div class="billing-panel" id="billingPanel">
    <div class="billing-header">
        <h3>Billing and Delivery Details</h3>
        <p>Provide these details if the pharmacy should arrange the medicine order for you.</p>
    </div>
    <div class="billing-grid">
        <label class="field-group">
            <span>Billing Name</span>
            <input type="text" name="billing_name" required>
        </label>
        <label class="field-group">
            <span>Phone Number</span>
            <input type="text" name="billing_phone" required>
        </label>
        <label class="field-group">
            <span>Email</span>
            <input type="email" name="billing_email" required>
        </label>
        <label class="field-group">
            <span>Collection Method</span>
            <select name="delivery_method" id="deliveryMethod">
                <option value="PICKUP">Pick up from pharmacy</option>
            </select>
        </label>
        <label class="field-group field-group-wide" id="billingAddressGroup">
            <span>Address</span>
            <textarea name="billing_address" rows="3"></textarea>
        </label>
        <label class="field-group">
            <span>City</span>
            <input type="text" name="billing_city">
        </label>
        <label class="field-group field-group-wide">
            <span>Notes</span>
            <textarea name="billing_notes" rows="3"></textarea>
        </label>
    </div>
</div>