package com.kalma.Login;

import android.app.DatePickerDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.DatePicker;
import android.widget.EditText;
import android.widget.Toast;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;

import com.android.volley.VolleyError;
import com.kalma.API_Interaction.APICaller;
import com.kalma.API_Interaction.ServerCallback;
import com.kalma.R;

import net.danlew.android.joda.JodaTimeAndroid;

import org.joda.time.DateTime;
import org.joda.time.DateTimeZone;
import org.joda.time.format.DateTimeFormat;
import org.joda.time.format.DateTimeFormatter;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.UnsupportedEncodingException;
import java.nio.charset.StandardCharsets;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.HashMap;
import java.util.Locale;
import java.util.Map;

//TODO Implement error handling and data validation

public class SignUpActivity extends AppCompatActivity {
    final Calendar myCalendar = Calendar.getInstance();
    EditText txtFirstName, txtLastName, txtPassword, txtEmail, txtDOB;
    Button signUp;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        JodaTimeAndroid.init(this);
        setContentView(R.layout.activity_sign_up);
        txtFirstName = findViewById(R.id.txtFirstName);
        txtLastName = findViewById(R.id.txtLastName);
        txtPassword = findViewById(R.id.txtPassword);
        txtEmail = findViewById(R.id.txtEmail);
        txtDOB = findViewById(R.id.txtDOB);
        signUp = findViewById(R.id.btnSignUp);


        final DatePickerDialog.OnDateSetListener date = new DatePickerDialog.OnDateSetListener() {
            //Convert datepicker value into string and fill textbox
            @Override
            public void onDateSet(DatePicker view, int year, int monthOfYear,
                                  int dayOfMonth) {
                myCalendar.set(Calendar.YEAR, year);
                myCalendar.set(Calendar.MONTH, monthOfYear);
                myCalendar.set(Calendar.DAY_OF_MONTH, dayOfMonth);
                SimpleDateFormat sdf = new SimpleDateFormat("dd/MM/yyyy", Locale.UK);
                txtDOB.setText(sdf.format(myCalendar.getTime()));

            }
        };

        signUp.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                String firstName = txtFirstName.getText().toString();
                String lastName = txtLastName.getText().toString();
                String password = txtPassword.getText().toString();
                String email = txtEmail.getText().toString();
                
                attemptSignUp(firstName, lastName, password, email);
            }
        });
        final DatePickerDialog datePicker = new DatePickerDialog(SignUpActivity.this);
        datePicker.setOnDateSetListener(date);
        txtDOB.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                datePicker.show();
            }
        });
        datePicker.setOnShowListener(new DialogInterface.OnShowListener() {
            @Override
            public void onShow(DialogInterface arg0) {
                datePicker.getButton(AlertDialog.BUTTON_NEGATIVE).setTextColor(getResources().getColor(R.color.NoColour, null));
                datePicker.getButton(AlertDialog.BUTTON_POSITIVE).setTextColor(getResources().getColor(R.color.YesColour, null));
            }
        });
    }

    private void attemptSignUp(String firstName, String lastName, String password, String email) {
        if (!(firstName.isEmpty() || lastName.isEmpty() || password.isEmpty() || email.isEmpty() || txtDOB.getText().toString().isEmpty())){
            if (validateString(firstName) && validateString(lastName)){
                //convert date string into DateTime object and generate epoch value
                DateTimeFormatter formatter = DateTimeFormat.forPattern("dd/MM/yyy");
                DateTime dateTimeGMT = new DateTime(formatter.parseDateTime(txtDOB.getText().toString()), DateTimeZone.UTC);
                signUp(firstName, lastName, password, email, dateTimeGMT.toString(DateTimeFormat.forPattern("yyyy-MM-dd'T'HH:mm:ss")));
            }
            else{
                Toast toast = Toast.makeText(getApplicationContext(), "First name and Last can only contain letters 'A' - 'Z'", Toast.LENGTH_LONG);
                toast.show();
            }
        }
        else{
            Toast toast = Toast.makeText(getApplicationContext(), "All fields must be filled.", Toast.LENGTH_LONG);
            toast.show();
        }
    }

    private void gotoLogin() {
        Intent intent = new Intent(this, LoginActivity.class);
        startActivity(intent);
    }
    private Map buildMap() {
        return new HashMap<String, String>();
    }
    private void signUp(String firstName, String lastName, String password, String email, String DOB) {
        APICaller apiCaller = new APICaller(getApplicationContext());
        apiCaller.post(false, buildSignUpJsonObject(firstName, lastName, password, email, DOB), buildMap(), getResources().getString(R.string.api_signup), new ServerCallback() {
                    @Override
                    public void onSuccess(JSONObject response) {
                        try {
                            //tell user that the sign-up was successful
                            String message = response.getString("message");
                            Toast toast = Toast.makeText(getApplicationContext(), message, Toast.LENGTH_LONG);
                            toast.show();
                            Log.d("Response", response.toString());
                            //open login page
                            gotoLogin();
                        } catch (JSONException je) {
                            Log.e("JSONException", "onErrorResponse: ", je);
                        }
                    }

                    @Override
                    public void onFail(VolleyError error) {
                        try {
                            //tell user that signup failed and what the issue was
                            String jsonInput = new String(error.networkResponse.data, "utf-8");
                            JSONObject responseBody = new JSONObject(jsonInput);
                            String message = responseBody.getString("message");
                            Log.w("Error.Response", jsonInput);
                            Toast toast = Toast.makeText(getApplicationContext(), message, Toast.LENGTH_LONG);
                            toast.show();
                        } catch (JSONException je) {
                            Log.e("JSONException", "onErrorResponse: ", je);
                        } catch (UnsupportedEncodingException err) {
                            Log.e("EncodingError", "onErrorResponse: ", err);
                        }
                    }
                }
        );
    }

    //validates that input string only contains letters
    public boolean validateString(String str) {
        if (str == null){
            return false;
        }
        str = str.toLowerCase();
        //Convert sting to char array and loop through letters individually
        char[] charArray = str.toCharArray();
        for (char ch : charArray) {
            //Check if letter value is between ascii 'a' and ascii 'z'
            if (!((ch >= 'a' && ch <= 'z') || (ch == 39) || (ch == 45))) {
                return false;
            }
        }
        return true;
    }

    private JSONObject buildSignUpJsonObject(String firstName, String lastName, String password, String email, String DOB) {
        //returns a json object based on input email and password
        JSONObject object = new JSONObject();
        try {
            object.put("email_address", email);
            object.put("password", password);
            object.put("first_name", firstName);
            object.put("last_name", lastName);
            object.put("date_of_birth", DOB);
        } catch (JSONException e) {
            e.printStackTrace();
        }
        return object;
    }

}
