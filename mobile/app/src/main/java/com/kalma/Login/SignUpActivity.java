package com.kalma.Login;

import androidx.appcompat.app.AppCompatActivity;
import android.app.DatePickerDialog;
import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.DatePicker;
import android.widget.EditText;
import android.widget.Toast;

import com.android.volley.VolleyError;
import com.kalma.API_Interaction.APICaller;
import com.kalma.API_Interaction.AuthStrings;
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
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Locale;

//TODO Implement error handling and data validation

public class SignUpActivity extends AppCompatActivity {
    EditText txtFirstName, txtLastName, txtPassword, txtEmail, txtDOB;
    final Calendar myCalendar = Calendar.getInstance();
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
                DateTimeFormatter formatter = DateTimeFormat.forPattern("dd/MM/yyy");
                DateTime dateTimeGMT = new DateTime(formatter.parseDateTime(txtDOB.getText().toString()), DateTimeZone.UTC);
                long epochSecs = (dateTimeGMT.getMillis() / 1000);
                signUp(firstName, lastName, password, email, epochSecs);

            }
        });

        txtDOB.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                DatePickerDialog datePicker = new DatePickerDialog(SignUpActivity.this, android.R.style.Theme_Holo_Dialog, date, 1990, 0, 0);
                datePicker.show();
            }
        });
    }



    private void signUp(String firstName, String lastName, String password, String email, long DOB) {
        APICaller apiCaller = new APICaller(getApplicationContext());

    }

    private JSONObject buildSignUpJsonObject(String firstName, String lastName, String password, String email, long DOB) {
        //returns a json object based on input email and password
        JSONObject object = new JSONObject();
        try {
            object.put("email_address", email);
            object.put("password",password);
            object.put("first_name",firstName);
            object.put("last_name", lastName);
            object.put("date_of_birth", DOB);
         } catch (JSONException e) {
            e.printStackTrace();
        }
        return object;
    }

}
