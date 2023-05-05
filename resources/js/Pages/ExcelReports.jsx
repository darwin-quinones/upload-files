import React, { useState } from "react";

export default function Dashboard() {
    const [files, setFiles] = useState('');
    //base end point url
    const FILE_UPLOAD_BASE_ENDPOINT = "http://localhost:8000";


    const generateReportComercializadorByPeriod = (event) => {
        event.preventDefault();
    };
    const generateReportComercializadorByDataRange = (event) => {
        event.preventDefault();
    };
    const generateReportOperatorByDataRange = (event) => {
        event.preventDefault();
    };
    const generateReportOperatorByPeriod = (event) => {
        event.preventDefault();
        const id_year = '2023';
        const id_month = '02';
        const report_code = 1;
        const filename = "Reporte Operadores - Periodo " + id_year + id_month  +".xlsx";

        const data = { id_month: id_month, id_year: id_year,  report_code: report_code};

        // fetch(FILE_UPLOAD_BASE_ENDPOINT + '/generar-reportes-excel', {
        //     method: 'POST',
        //     headers: {
        //         'Content-Type': 'application/json'
        //     },
        //     body: JSON.stringify(data)
        // })
        //     .then(response => response.json())
        //     .then(data => console.log(data))
        //     .catch(error => console.error(error));

            fetch(FILE_UPLOAD_BASE_ENDPOINT + '/generar-reportes-excel', {
                method: 'POST',
                body:  JSON.stringify(data),
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                // Check if the response was successful
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                // Parse the response as a blob
                console.log(response)
                return response.blob();
            })
            .then(blob => {
                console.log(blob)
                // Create a download link for the blob
                const url = window.URL.createObjectURL(new Blob([blob]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', filename);
                document.body.appendChild(link);
                // Trigger the download link
                link.click();
                // Cleanup the link and object URL
                link.parentNode.removeChild(link);
                window.URL.revokeObjectURL(url);
            })
            .catch(error => {
                console.error('There was a problem with the fetch operation:', error);
            });

    };



    return (

        <div>
            <h>Aqui podras generar reportes en excel</h> <br />
            <button type="submit" onClick={generateReportOperatorByPeriod}>Generar Reporte operador por periodo</button><br />
            <button type="submit" onClick={generateReportOperatorByDataRange}>Generar Reporte operador por rango</button><br />
            <button type="submit" onClick={generateReportComercializadorByPeriod}>Generar Reporte comercializadores por periodo</button><br />
            <button type="submit" onClick={generateReportComercializadorByDataRange}>Generar Reporte comercializadores por rango</button><br />
        </div>

    );
}
