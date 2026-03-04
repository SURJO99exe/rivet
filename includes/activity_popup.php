<!-- Live Activity Popup -->
<div id="activity-popup" style="position: fixed; bottom: -150px; left: 20px; background: white; padding: 12px 20px; border-radius: 1rem; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 12px; z-index: 99999; transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55); border-left: 5px solid #10b981; width: calc(100% - 40px); max-width: 320px;">
    <div id="activity-icon" style="width: 40px; height: 35px; background: #f0fdf4; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 800; color: #10b981; flex-shrink: 0;">
        $
    </div>
    <div style="flex: 1; min-width: 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1px;">
            <h4 style="font-size: 0.9rem; font-weight: 800; color: #1e293b; margin: 0;">Live Activity</h4>
            <span onclick="hideActivity()" style="cursor: pointer; opacity: 0.4; font-size: 1.1rem; line-height: 1; padding: 0 5px;">&times;</span>
        </div>
        <p style="font-size: 0.8rem; color: #64748b; margin: 0; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
            Someone from <strong id="act-country" style="color: #6366f1;">Japan</strong> <span id="act-action">earned</span> <strong id="act-amount" style="color: #10b981;">$0.0500</strong> <span id="act-type">from survey</span>
        </p>
        <p id="activity-time" style="font-size: 0.7rem; color: #94a3b8; margin-top: 3px;">9 sec ago</p>
    </div>
</div>

<style>
    @media (max-width: 480px) {
        #activity-popup {
            left: 10px !important;
            width: calc(100% - 20px) !important;
            padding: 10px 15px !important;
        }
    }
</style>

<script>
    function showActivity() {
        const popup = document.getElementById('activity-popup');
        const countryElem = document.getElementById('act-country');
        const actionElem = document.getElementById('act-action');
        const amountElem = document.getElementById('act-amount');
        const typeElem = document.getElementById('act-type');
        const timeElem = document.getElementById('activity-time');
        const iconElem = document.getElementById('activity-icon');
        
        const pathPrefix = '<?php echo (strpos($_SERVER['PHP_SELF'], '/user/') !== false ? '../' : ''); ?>';
        
        fetch(pathPrefix + 'api/live_activity.php')
            .then(res => res.json())
            .then(data => {
                countryElem.innerText = data.country;
                actionElem.innerText = data.action;
                amountElem.innerText = data.amount;
                typeElem.innerText = data.type;
                timeElem.innerText = data.time;
                
                // Set green for earned, indigo for others
                if (data.action === 'withdrawn') {
                    popup.style.borderLeftColor = '#6366f1';
                    iconElem.style.color = '#6366f1';
                    iconElem.style.background = '#f5f3ff';
                    iconElem.innerText = '💳';
                } else {
                    popup.style.borderLeftColor = '#10b981';
                    iconElem.style.color = '#10b981';
                    iconElem.style.background = '#f0fdf4';
                    iconElem.innerText = '$';
                }
                
                popup.style.bottom = '20px';
                
                // Show for 5 seconds, then hide and trigger next after a short gap
                setTimeout(() => {
                    hideActivity();
                    setTimeout(showActivity, 1500); // Wait 1.5s after hiding before showing next
                }, 5000);
            })
            .catch(err => {
                console.error('Activity Error:', err);
                setTimeout(showActivity, 5000); // Retry after 5s on error
            });
    }

    function hideActivity() {
        const popup = document.getElementById('activity-popup');
        if (popup) {
            popup.style.bottom = '-150px';
        }
    }

    // Start the recursive cycle after a short initial delay
    setTimeout(showActivity, 2000);
</script>
